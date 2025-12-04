<?php

declare(strict_types=1);

namespace App\Api\Cryptobot;

use App\Api\ApiClient;
use App\Api\Cryptobot\Requests\CryptobotCreateInvoceRequest;
use App\Api\Cryptobot\Requests\CryptobotTransferRequest;
use App\Api\Cryptobot\Responses\CryptobotCreateInvoseResponse;
use App\Api\Cryptobot\Responses\CryptobotExchangeRates;
use App\Api\Cryptobot\Responses\CryptobotTransferResponse;
use App\Enums\Currencies\CurrencyEnum;
use App\Enums\Payments\PaymentSystemEnum;
use App\Services\Payments\DTO\PaymnetSystemBalanceDTO;
use Illuminate\Support\Facades\Log;

class CryptobotApiClient extends ApiClient
{
    public function transfer(CryptobotTransferRequest $request): CryptobotTransferResponse
    {
        Log::info('Withdraw CryptoBot postData', $request->toArray());

        $response = $this->post(
            'transfer',
            [
                'json' => $request->toArray(),
                'headers' => [
                    "Crypto-Pay-API-Token" => config('api-clients.cryptobot.app_token'),
                    "Content-Type" => "application/json",
                ]
            ]
        );

        Log::info('Withdraw response ', [$response]);

        $data = json_decode($response->getBody()->getContents(), true);

        return new CryptobotTransferResponse($data['result']['status']);
    }

    public function createInvoce(CryptobotCreateInvoceRequest $request): CryptobotCreateInvoseResponse
    {
        Log::info('Withdraw CryptoBot postData', $request->toArray());

        $response = $this->post(
            'createInvoice',
            [
                'json' => $request->toArray(),
                'headers' => [
                    "Crypto-Pay-API-Token" => config('api-clients.cryptobot.app_token'),
                    "Content-Type" => "application/json",
                ]
            ]
        );

        $responseBody = $response->getBody()->getContents();
        $data = json_decode($responseBody, true);

        // Проверяем наличие ошибок в ответе
        if (isset($data['error'])) {
            $errorMessage = $data['error']['description'] ?? $data['error']['name'] ?? 'Unknown error';
            Log::error('Cryptobot API error', [
                'error' => $data['error'],
                'request' => $request->toArray(),
                'response' => $data
            ]);
            throw new \Exception('Cryptobot API error: ' . $errorMessage);
        }

        // Проверяем наличие необходимых полей
        if (!isset($data['result'])) {
            Log::error('Cryptobot API invalid response', ['response' => $data]);
            throw new \Exception('Cryptobot API returned invalid response');
        }

        if (!isset($data['result']['status'])) {
            Log::error('Cryptobot API missing status', ['response' => $data]);
            throw new \Exception('Cryptobot API response missing status field');
        }

        if (!isset($data['result']['invoice_id'])) {
            Log::error('Cryptobot API missing invoice_id', ['response' => $data]);
            throw new \Exception('Cryptobot API response missing invoice_id field');
        }

        // Используем pay_url если есть, иначе mini_app_invoice_url
        $url = $data['result']['pay_url'] ?? $data['result']['mini_app_invoice_url'] ?? null;
        
        if (empty($url)) {
            Log::error('Cryptobot API missing URL', ['response' => $data]);
            throw new \Exception('Cryptobot API response missing payment URL');
        }

        return new CryptobotCreateInvoseResponse(
            $data['result']['status'], 
            $data['result']['invoice_id'], 
            $url
        );
    }

    public function balance(): array
    {
        $response = $this->get(
            'getBalance',
            [
                'headers' => [
                    "Crypto-Pay-API-Token" => config('api-clients.cryptobot.app_token'),
                    "Content-Type" => "application/json",
                ]
            ]
        );

        $data = collect(json_decode($response->getBody()->getContents(), true)['result']);
        $balances = [];
        foreach (CurrencyEnum::cases() as $item) {
            if (!$data->where('currency_code', $item->value)->isEmpty()) {
                $balances[] = new PaymnetSystemBalanceDTO(
                    PaymentSystemEnum::CRYPTOBOT,
                    $data->where('currency_code', $item->value)->first()['available'],
                    $item
                );
            }
        }
        return $balances;
    }

    public function getExchangeRates(): CryptobotExchangeRates
    {
        $response = $this->get('getExchangeRates');

        $data = json_decode($response->getBody()->getContents(), true);

        return new CryptobotExchangeRates($data['result']);
    }
}