<?php

declare(strict_types=1);

namespace App\Api\Gotham;

use App\Api\Gotham\Requests\GothamCreatePaymentRequest;
use App\Api\Gotham\Responses\CreateCardNumberOrderResponse;
use FKS\Api\ApiClient;
use FKS\Serializer\SerializerFacade;

class GothamApiClient extends ApiClient
{
    public function createCardNumberOrder(GothamCreatePaymentRequest $request): CreateCardNumberOrderResponse
    {
        $response = $this->client->post(
            'v1/make_order/pay_in',
            [
                'json' => [
                    'amount' => $request->amount,
                    'currency' => strtolower($request->currency->value),
                    'traffic_type' => 'card_number',
                    'callback_url' => route(
                        'vpi.v1.payments.callback',
                        ['secret' => $request->callbackSecret]
                    ),
                    'external_id' => $request->externalId,
                ],
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['response_code'])) {
            throw new \DomainException($data['message']);
        }

        return SerializerFacade::deserializeFromArray($data, CreateCardNumberOrderResponse::class);
    }

    public function createSBPOrder(GothamCreatePaymentRequest $request): CreateCardNumberOrderResponse
    {
        $response = $this->client->post(
            'v1/make_order/pay_in',
            [
                'json' => [
                    'amount' => $request->amount,
                    'currency' => strtolower($request->currency->value),
                    'traffic_type' => 'sbp',
                    'callback_url' => route(
                        'vpi.v1.payments.callback',
                        ['secret' => $request->callbackSecret]
                    ),
                    'external_id' => $request->externalId,
                ],
            ]
        );

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['response_code'])) {
            throw new \DomainException($data['message']);
        }

        return SerializerFacade::deserializeFromArray($data, CreateCardNumberOrderResponse::class);
    }
}