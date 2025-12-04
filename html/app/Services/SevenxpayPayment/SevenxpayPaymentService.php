<?php

namespace App\Services\SevenxpayPayment;

use Exception;
use Illuminate\Support\Facades\Log;

class SevenxpayPaymentService
{
    private string $publicKey;
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->publicKey = config('services.7xpay.public_key');
        $this->secretKey = config('services.7xpay.secret_key');
        $this->baseUrl = config('services.7xpay.base_url');
    }

    public function createPayment(array $paymentData): array
    {
        try {
            $public_key = $this->publicKey;
            $params = $paymentData;


            Log::info('Attempting to create payment', [
                'params' => $params,
                'signature' => $public_key
            ]);

            $response = $this->sendRequest(
                'form/payment/card',
                $params,
                $public_key
            );

            return $response;
        } catch (Exception $e) {
            Log::error('7xPay payment creation failed', [
                'error' => $e->getMessage(),
                'params' => $paymentData
            ]);

            throw new Exception('Payment creation failed: ' . $e->getMessage());
        }
    }

  

    private function sendRequest(string $endpoint, array $params, string $public_key): array
    {
        $curl = curl_init();

        $headers = [
            'Content-Type: application/json'
        ];

        $headers = [
            'API-Key: ' . $public_key,
            'Content-Type: application/json'
        ];

        $url = $this->baseUrl . '' . $endpoint;

        $jsonParams = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Log::info('Sending request to 7xPay', [
            'url' => $url,
            'params' => $params,
            'headers' => $headers
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonParams,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);

        $response = curl_exec($curl);
        $errorNo = curl_errno($curl);
        $errorMessage = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($errorNo) {
            Log::error('Curl request failed', [
                'error_no' => $errorNo,
                'error' => $errorMessage,
                'url' => $url
            ]);
            throw new Exception("Curl request failed: $errorMessage");
        }

        if ($httpCode >= 400) {
            Log::error('API request failed', [
                'http_code' => $httpCode,
                'response' => $response,
                'url' => $url
            ]);
            throw new Exception("API request failed with status $httpCode");
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON response', [
                'response' => $response,
                'json_error' => json_last_error_msg()
            ]);
            throw new Exception('Invalid JSON response from API');
        }

        Log::info('Received response from 7xpay', [
            'response' => $decodedResponse
        ]);

        return $decodedResponse;
    }
}
