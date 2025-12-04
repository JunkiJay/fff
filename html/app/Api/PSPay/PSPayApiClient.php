<?php

declare(strict_types=1);

namespace App\Api\PSPay;

use App\Api\Paradise\Requests\ParadisePayRequest;
use App\Api\Paradise\Responses\ParadiseOrderCreateResponse;
use FKS\Api\ApiClient;

class PSPayApiClient extends ApiClient
{
    public function pay(ParadisePayRequest $request): ParadiseOrderCreateResponse
    {
        $response = $this->post(
            'payments',
            [
                'json' => [
                    'merchant_customer_id' => (string)$request->paymentId,
                    'return_url' => config('app.url'),
                    'amount' => $request->amount,
                    'description' => $request->userId . "@paradise.info",
                    'ip' => $request->ip,
                ],
                'headers' => [
                    "merchant-id" => config('api-clients.pspay.shop_id'),
                    "merchant-secret-key" => config('api-clients.pspay.api_secret'),
                    "Content-Type" => 'application/json',
                ]
            ]
        );
        return $this->handleResponse($response, ParadiseOrderCreateResponse::class);
    }
}

