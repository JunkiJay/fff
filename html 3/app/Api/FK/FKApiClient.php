<?php

declare(strict_types=1);

namespace App\Api\FK;

use App\Api\FK\Responses\FKHistoryResponse;
use App\Enums\Currencies\CurrencyEnum;
use App\Enums\Payments\PaymentSystemEnum;
use App\Services\Payments\DTO\PaymnetSystemBalanceDTO;
use Carbon\Carbon;
use FKS\Api\ApiClient;
use GuzzleHttp\Exception\ClientException;

class FKApiClient extends ApiClient
{
    public function transfer(string $toWallet, int $amount, string $idempotenceKey): int
    {
        $data = [
            'amount' => $amount,
            'currency_id' => 1,
            'fee_from_balance' => 1,
            'idempotence_key' => $idempotenceKey,
            'to_wallet_id' => (int)str_replace('F', '', $toWallet),
        ];

        $publicKey = config('api-clients.fk.public_key');

        try {
            $response = $this->post(
                "$publicKey/transfer",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->makeSignature($data),
                    ]
                ]
            );
        } catch (ClientException $e) {
            $data = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new \Exception($data['message']);
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['data']['id'];
    }

    public function withdraw(string $toWalet, int $amount, string $idempotenceKey)
    {
        $data = [
            'account' => $toWalet,
            'amount' => $amount,
            'currency_id' => 1,
            'fee_from_balance' => 0,
            'idempotence_key' => $idempotenceKey,
            'payment_system_id' => 27,
        ];

        $publicKey = config('api-clients.fk.public_key');

        try {
            $response = $this->post(
                "$publicKey/withdrawal",
                [
                    'json' => $data,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->makeSignature($data),
                    ]
                ]
            );
        } catch (ClientException $e) {
            dd(json_decode($e->getResponse()->getBody()->getContents()));
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['data']['id'];
    }

    public function paymentSystems()
    {
        $publicKey = config('api-clients.fk.public_key');

        $response = $this->get(
            "$publicKey/payment_systems",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->makeSignature([]),
                ]
            ]
        );

        return json_decode($response->getBody()->getContents(), true);
    }

    public function balance(): array
    {
        $balances = [];
        $publicKey = config('api-clients.fk.public_key');

        $response = $this->get(
            "$publicKey/balance",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->makeSignature([]),
                ]
            ]
        );

        $balancesResponse = collect(json_decode($response->getBody()->getContents(), true)['data']);

        foreach (CurrencyEnum::cases() as $item) {
            if (!$balancesResponse->where('currency_code', $item->value)->isEmpty()) {
                $balances[] = new PaymnetSystemBalanceDTO(
                    PaymentSystemEnum::FK,
                    $balancesResponse->where('currency_code', $item->value)->first()['value'],
                    $item
                );
            }
        }

        return $balances;
    }

    public function history(Carbon $from, Carbon $to)
    {
        return $this->walkPages(
            function ($page, $limit) use ($from, $to) {
                $publicKey = config('api-clients.fk.public_key');
                sleep(1);
                return $this->handleResponse(
                    $this->get(
                        "/v1/$publicKey/history?{$from->format('Y-m-d')}&{$to->format('Y-m-d')}&page=$page&limit=$limit",
                        [
                            'headers' => [
                                'Authorization' => 'Bearer ' . $this->makeSignature([]),
                            ]
                        ]
                    ),
                    FKHistoryResponse::class
                );
            },
            1,
            10
        );
    }

    private function makeSignature(array $data): string
    {
        $key = config('api-clients.fk.private_key');

        if ($data !== []) {
            ksort($data);
            $key = json_encode($data) . $key;
        }

        return hash('sha256', $key);
    }
}