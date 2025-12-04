<?php

declare(strict_types=1);

namespace App\Api\Blvckpay;

use App\Api\Blvckpay\DTO\CreateSbpOrderResonse;
use FKS\Api\ApiClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class BlvckpayApiClient extends ApiClient
{
    public function createSbpOrder(int|float $amount): CreateSbpOrderResonse
    {
        Log::info(
            'Create blvckpay SBP order',
            [
                '/sbp/order/create',
                [
                    'json' => [
                        'amount' => $amount,
                        'signature' => config('api-clients.blvckpay.signature'),
                    ]

                ]
            ]
        );
        try {
            $response = $this->post(
                '/sbp/order/create',
                [
                    'json' => [
                        'amount' => $amount,
                        'signature' => config('api-clients.blvckpay.signature'),
                    ]

                ]
            );
        } catch (ClientException $exception) {
            Log::error('Create SBP order error', [$exception->getResponse()->getBody()->getContents()]);;
            throw $exception;
        }

        $data = json_decode($response->getBody()->getContents(), true);

        Log::debug($data);

        return new CreateSbpOrderResonse($data['status'], $data['url'], $data['order_id']);
    }

    public function getQR(string $orderId): string
    {
        try {
            $response = $this->get(
                "/apiv1/wallet/widgets?order_id={$orderId}",
                [
                    'json' => [
                        'signature' => config('api-clients.blvckpay.signature'),
                    ]

                ]
            );
            return json_decode($response->getBody()->getContents(), true)['sbp_url'];
        } catch (ClientException $exception) {
            Log::error('Create SBP order error', [$exception->getResponse()->getBody()->getContents()]);;
            throw $exception;
        }
    }
}