<?php

declare(strict_types=1);

namespace App\Api\Expay;

use App\Api\Expay\Requests\CreatePaymentRequest;
use App\Api\Expay\Responses\ExpayCreatePaymentResponse;
use FKS\Api\ApiClient;

class ExpayApiClient extends ApiClient
{
    public function createPayment(CreatePaymentRequest $request): ExpayCreatePaymentResponse
    {
        $requestBody = [
            'refer_type' => 'p2p_payform',
            'token' => 'CARDRUBP2P',
            'sub_token' => 'CARDRUB',
            'amount' => $request->amount,
            'client_transaction_id' => $request->clientTransactionId,
            'client_merchant_id' => '0',
            'fingerprint' => $request->fingerprint,
            'call_back_url' => 'https://stimule1.win/callback/expay34584734343434',
            'alter_refer_custom_options' => ['redirect_url' => 'https://stimule1.win']
        ];

        $privateKey = config('api-clients.expay.private_key');


        $requestBodyJson = json_encode($requestBody);
        $timestamp = time();
        $message = $timestamp . $requestBodyJson;
        $signature = hash_hmac('sha512', $message, $privateKey);


        $response = $this->post(
            'transaction/create/in',
            [
                'json' => $requestBody,
                'headers' => [
                    'ApiPublic' => 'mqbkjmrfgbx9dz05kdhx7g1v28n5doqbee7lpdfaco1v537kfbmwjyo7n91hxidl',
                    'Timestamp' => $timestamp,
                    'Signature' => $signature,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]
        );

        return $this->handleResponse($response, ExpayCreatePaymentResponse::class);
    }
}