<?php

namespace App\Services\BovaPayment;

use Exception;
use Illuminate\Support\Facades\Log;

class BovaPaymentService
{
    private string $userUuid;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->userUuid = config('services.bova.user_uuid');
        $this->apiKey = config('services.bova.api_key');
        $this->baseUrl = config('services.bova.base_url');
    }

    public function createPayment(array $paymentData): array
    {
        try {
            $params = $this->preparePaymentParams($paymentData);
            $signature = $this->generateSignature($params);

            Log::info('Attempting to create payment', [
                'params' => $params,
                'signature' => $signature
            ]);

            $response = $this->sendRequest(
                'merchant/v1/deposits',
                $params,
                $signature
            );

            return $response;
        } catch (Exception $e) {
            Log::error('Bova payment creation failed', [
                'error' => $e->getMessage(),
                'params' => $paymentData
            ]);

            throw new Exception('Payment creation failed: ' . $e->getMessage());
        }
    }

    private function preparePaymentParams(array $paymentData): array
    {
        return [
            'user_uuid' => $this->userUuid,
            'merchant_id' => $paymentData['merchant_id'],
            'amount' => $paymentData['amount'],
            'callback_url' => $paymentData['callback_url'],
            'redirect_url' => $paymentData['redirect_url'],
            "email" => $paymentData['email'],
            'currency' => $paymentData['currency'],
            'payeer_identifier' => $paymentData['payeer_identifier'],
            'payeer_ip' => $paymentData['payeer_ip'],
            'payeer_type' => $paymentData['payeer_type'],
            'payment_method' => $paymentData['payment_method'],
            'payeer_bank_name' => $paymentData['payeer_bank_name']
        ];
    }

    private function generateSignature(array $params): string
    {
        $signParametrs = [
            'user_uuid' => $params['user_uuid'],
            'merchant_id' => $params['merchant_id'],
            'amount' => $params['amount'],
            'callback_url' => $params['callback_url'],
            'redirect_url' => $params['redirect_url'],
            "email" => $params['email'],
            'currency' => $params['currency'],
            'payeer_identifier' => $params['payeer_identifier'],
            'payeer_ip' => $params['payeer_ip'],
            'payeer_type' => $params['payeer_type'],
            'payment_method' => $params['payment_method'],
            'payeer_bank_name' => $params['payeer_bank_name']
        ];
        
        $jsonString = json_encode($signParametrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $dataToSign = $this->apiKey . $jsonString;

        return sha1($dataToSign);
    }

    private function sendRequest(string $endpoint, array $params, string $signature): array
    {
        $curl = curl_init();

        $headers = [
            'Signature: ' . $signature,
            'Content-Type: application/json'
        ];

        $url = $this->baseUrl . '' . $endpoint;

        $jsonParams = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Log::info('Sending request to Bova', [
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

        Log::info('Received response from Bova', [
            'response' => $decodedResponse
        ]);

        return $decodedResponse;
    }

    public function validateWebhookSignature(array $data, string $signature): bool
    {
        $calculatedSignature = $this->generateSignature($data);
        return hash_equals($calculatedSignature, $signature);
    }
}
