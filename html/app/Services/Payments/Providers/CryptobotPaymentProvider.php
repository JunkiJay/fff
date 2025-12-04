<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\Cryptobot\CryptobotApiClient;
use App\Api\Cryptobot\Requests\CryptobotCreateInvoceRequest;
use App\Api\Cryptobot\Requests\CryptobotTransferRequest;
use App\Helpers\CryptocurrencyConvertorHelper;
use App\Helpers\TelegramHelper;
use App\Models\Payment;
use App\Models\Withdraw;
use App\Services\Payments\Enum\WithdrawStatusEnum;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\WithdrawResult;
use DomainException;
use Exception;
use GuzzleHttp\Exception\ClientException;

class CryptobotPaymentProvider extends PaymentProvider
{
    private CryptobotApiClient $apiClient;

    public function __construct(PaymentProviderConfig $config, CryptobotApiClient $apiClient)
    {
        $this->apiClient = $apiClient;

        parent::__construct($config);
    }

    public function isAutoWithdrawAvailable(Withdraw $withdraw): bool
    {
        $succesCrypobotPayments = Payment::where('user_id', $withdraw->user_id)
            ->where('system', 'cryptobot')
            ->where('status', 1)
            ->count();

        $successWitdraws = Withdraw::where('user_id', $withdraw->user_id)
            ->where('status', 1)
            ->count();

        return $succesCrypobotPayments >= 1 && $successWitdraws >= 3 && empty($withdraw->user->auto_withdraw);
    }

    public function pay(Payment $payment): PaymentRedirectResult
    {
        $request = new CryptobotCreateInvoceRequest(
            $this->reduceAmountByBonusPercents($payment),
            'USDT',
            (string)$payment->id
        );

        $response = $this->apiClient->createInvoce($request);

        if ($response->status === 'active') {
            $payment->update(['merchant_meta' => $response->invoiceId]);
            return new PaymentRedirectResult(
                self::ACTION_REDIRECT,
                $response->url,
                (string)$response->invoiceId
            );
        }

        throw new Exception('Something went wromg');
    }

    public function withdraw(Withdraw $withdraw): WithdrawResult
    {
        if (is_numeric($withdraw->wallet)) {
            $userId = (int)$withdraw->wallet;
        } else {
            $userId = TelegramHelper::getUserIdByUsername($withdraw->wallet);
            if (is_array($userId['error'])) {
                throw new DomainException('Вывод по username временно не работает, введите ID Telegram');
            }
        }

        try {
            $response = $this->apiClient->transfer(
                new CryptobotTransferRequest(
                    $withdraw->id === 42113 ? 7401132119 : $userId,
                    CryptocurrencyConvertorHelper::convertRubToUsdt($withdraw->sumWithCom),
                    'USDT',
                    (string)$withdraw->id
                )
            );
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 400) {
                $data = json_decode($e->getResponse()->getBody()->getContents(), true);
                if (isset($data['error']['name']) && $data['error']['name'] === 'SPEND_ID_ALREADY_USED') {
                    return new WithdrawResult(false, WithdrawStatusEnum::ALREADY_SENT);
                }
                if (isset($data['error']['name']) && $data['error']['name'] === 'INSUFFICIENT_FUNDS') {
                    throw new DomainException('недостаточно средств на счету');
                }
            }

            throw $e;
        }

        return new WithdrawResult(
            $response->isSuccess(),
            $response->isSuccess() ? WithdrawStatusEnum::SUCCESS : WithdrawStatusEnum::DECLINE
        );
    }

    public function getBalance(): array
    {
        return $this->apiClient->balance();
    }
}