<?php

declare(strict_types=1);

namespace App\Api\Paradise;
use App\Api\Paradise\Requests\ParadisePayRequest;
use App\Api\Paradise\Responses\ParadiseOrderCreateResponse;
use App\Helpers\JsonFixer;
use FKS\Api\ApiClient;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ParadiseApiClient extends ApiClient
{
    public function pay(ParadisePayRequest $request): ParadiseOrderCreateResponse
    {
        // Хак: вызываем Paradise так же, как рабочий curl, в обход Guzzle,
        // чтобы гарантированно получать JSON, а не HTML index.html
        $baseUrl   = (string) config('api-clients.paradise.base_url');
        $shopId    = (string) config('api-clients.paradise.shop_id');
        $apiSecret = (string) config('api-clients.paradise.api_secret');

        $url = rtrim($baseUrl, '/') . '/payments';

        $payload = [
            'amount' => (int) $request->amount,                     // в копейках
            'payment_method' => 'sbp',
            'merchant_customer_id' => (string) $request->paymentId, // ID платежа на нашей стороне
            'ip' => $request->ip,
            'return_url' => config('app.url'),
            'description' => mb_substr((string)$request->userId . "@paradise.info", 0, 128),
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        Log::info('ParadiseApiClient(curl): sending request', [
            'url' => $url,
            'shop_id' => $shopId,
            'payload' => $payload,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            // ОБХОД DDoS-Guard: отключаем прокси, если он настроен
            CURLOPT_PROXY => '', // Пустая строка отключает прокси
            CURLOPT_PROXYUSERPWD => '', // Отключаем авторизацию прокси
            CURLOPT_HTTPPROXYTUNNEL => false,
            // ОБХОД DDoS-Guard: добавляем заголовки, которые могут помочь
            CURLOPT_HTTPHEADER => [
                'merchant-id: ' . $shopId,
                'merchant-secret-key: ' . $apiSecret,
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: curl/8.1.0',
                // Дополнительные заголовки для обхода DDoS-Guard
                'Connection: keep-alive',
                'Cache-Control: no-cache',
                'Pragma: no-cache',
            ],
            CURLOPT_POSTFIELDS => $jsonPayload,
            // ОБХОД DDoS-Guard: не следовать редиректам (они могут вести на js-challenge)
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            // ОБХОД DDoS-Guard: таймауты
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            // ОБХОД DDoS-Guard: SSL настройки
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $rawBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        Log::info('ParadiseApiClient(curl): response meta', [
            'http_code' => $httpCode,
            'curl_error' => $curlError ?: null,
            'raw_body_preview' => is_string($rawBody) ? mb_substr($rawBody, 0, 300) : null,
        ]);

        if ($rawBody === false || $curlError) {
            throw new RuntimeException('Paradise curl error: ' . ($curlError ?: 'unknown error'));
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            Log::error('ParadiseApiClient(curl): non-2xx status', [
                'http_code' => $httpCode,
                'body' => $rawBody,
            ]);
            throw new RuntimeException('Paradise returned HTTP ' . $httpCode);
        }

        try {
            // Пытаемся починить/декодировать JSON через JsonFixer
            $data = JsonFixer::decode($rawBody);
        } catch (\Throwable $e) {
            Log::error('ParadiseApiClient: invalid JSON response', [
                'error' => $e->getMessage(),
                'raw_body' => $rawBody,
            ]);

            throw new RuntimeException('Invalid JSON response from Paradise: ' . $e->getMessage());
        }

        try {
            // Маппим массив в ParadiseOrderCreateResponse через Serializer
            $serializer = $this->getSerializerInstance();
            /** @var ParadiseOrderCreateResponse $result */
            $result = $serializer->deserializeFromArray($data, ParadiseOrderCreateResponse::class, []);
            return $result;
        } catch (\Throwable $e) {
            Log::error('ParadiseApiClient: failed to deserialize response', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            throw new RuntimeException('Failed to deserialize Paradise response: ' . $e->getMessage());
        }
    }
}
