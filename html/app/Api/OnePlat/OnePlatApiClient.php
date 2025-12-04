<?php

declare(strict_types=1);

namespace App\Api\OnePlat;

use App\Api\OnePlat\Requests\OnePlatPayRequest;
use App\Api\OnePlat\Responses\OnePlatCreateResponse;
use FKS\Api\ApiClient;

class OnePlatApiClient extends ApiClient
{
    public function createPayment(OnePlatPayRequest $request): OnePlatCreateResponse
    {
        $response = $this->post(
            'merchant/order/create/by-api',
            [
                'json' => [
                    'merchant_order_id' => $request->paymentId,
                    'user_id' => $request->userId,
                    'amount' => $request->amount,
                    'email' => $request->userId . "@sweetx1.pro",
                    'method' => 'sbp'
                ],
                'headers' => [
                    'x-shop' => config('api-clients.1plat.shop_id'),
                    'x-secret' => config('api-clients.1plat.secret'),
                    'Content-Type' => 'application/json',
                ]
            ]
        );

        return $this->handleResponse($response, OnePlatCreateResponse::class);
    }
}