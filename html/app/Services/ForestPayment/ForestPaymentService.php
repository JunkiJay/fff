<?php

namespace App\Services\ForestPayment;

use Exception;
use Illuminate\Support\Facades\Log;

class ForestPaymentService
{
    private string $key_1;
    private string $key_2;
    private string $merchand_id;
    private string $base_url;

    public function __construct()
    {
        $this->key_1 = config('services.forest.key_1');
        $this->key_2 = config('services.forest.key_2');
        $this->merchand_id = config('services.forest.merchand_id');
        $this->base_url = config('services.forest.base_url');
    }

    public function createPayment(array $paymentData): array
    {
        try {
            $signature = $this->generateSignature($paymentData);
            $params = $this->preparePaymentParams($paymentData, $signature);


            Log::info('Attempting to create payment', [
                'params' => $params,
                'signature' => $signature
            ]);

            $response = $this->sendRequest(
                'api/payments',
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

    private function preparePaymentParams(array $paymentData, string $signature): array
    {
        return [
            'shop_id' => $paymentData['shop_id'],
            'invoice_id' => $paymentData['invoice_id'],
            'description' => $paymentData['description'],
            'amount' => $paymentData['amount'],
            'method' => $paymentData['method'],
            'country' => $paymentData['country'],
            'currency' => $paymentData['currency'],
            'success_url' => $paymentData['success_url'],
            'fail_url' => $paymentData['fail_url'],
            'signature' => $signature,
        ];
    }

    private function generateSignature(array $params): string
    {

        $dataToSign = $params['shop_id'] . ':' . $params['invoice_id'] . ':' . $params['amount'] . ':' . $params['method'] . ':' . $this->key_1;

        return md5($dataToSign);
    }

    private function sendRequest(string $endpoint, array $params, string $signature): array
    {
        $curl = curl_init();

        $headers = [
            'Content-Type: application/json'
        ];

        $url = $this->base_url . '' . $endpoint;

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
}
