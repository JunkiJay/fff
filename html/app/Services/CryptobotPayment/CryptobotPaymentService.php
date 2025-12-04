<?php

namespace App\Services\CryptobotPayment;

use Exception;
use Illuminate\Support\Facades\Log;

class CryptobotPaymentService
{
    private string $appToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->appToken = config('services.cryptobot.app_token');
        $this->baseUrl = config('services.cryptobot.base_url');
    }

    public function createPayment(array $paymentData): array
    {
        try {
            $appToken = $this->appToken;
            $params = $paymentData;


            Log::info('Attempting to create payment', [
                'params' => $params,
                'appToken' => $appToken
            ]);

            $response = $this->sendRequest(
                'api/createInvoice',
                $params,
                $appToken
            );

            return $response;
        } catch (Exception $e) {
            Log::error('Cryptobot payment creation failed', [
                'error' => $e->getMessage(),
                'params' => $paymentData
            ]);

            throw new Exception('Payment creation failed: ' . $e->getMessage());
        }
    }



    private function sendRequest(string $endpoint, array $params, string $appToken): array
    {
        $curl = curl_init();


        $headers = [
            'Crypto-Pay-API-Token: ' . $appToken,
            'Content-Type: application/json'
        ];

        $url = $this->baseUrl . '' . $endpoint;

        $jsonParams = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Log::info('Sending request to Cryptobot', [
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

        Log::info('Received response from Cryptobot', [
            'response' => $decodedResponse
        ]);

        return $decodedResponse;
    }

    public function createWithdraw(array $paymentData): array
    {
        try {
            $appToken = $this->appToken;
            $params = $paymentData;


            Log::info('Attempting to create withdraw', [
                'params' => $params,
                'appToken' => $appToken
            ]);

            $response = $this->sendRequest(
                'api/createCheck',
                $params,
                $appToken
            );

			
            return $response;
        } catch (Exception $e) {
            Log::error('Cryptobot withdraw creation failed', [
                'error' => $e->getMessage(),
                'params' => $paymentData
            ]);

            throw new Exception('Withdraw creation failed: ' . $e->getMessage());
        }
    }
}
