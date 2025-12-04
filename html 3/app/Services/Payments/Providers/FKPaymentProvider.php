<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\FK\FKApiClient;
use App\Models\Payment;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Payments\Enum\WithdrawStatusEnum;
use App\Services\Payments\Facades\PaymentServiceFacade;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\WithdrawResult;
use Illuminate\Support\Facades\Log;

class FKPaymentProvider extends PaymentProvider
{
    public function __construct(
        PaymentProviderConfig $config,
        public readonly FKApiClient $client
    ) {
        parent::__construct($config);
    }

    public function pay(Payment $payment): PaymentRedirectResult
    {
        $amount = $this->reduceAmountByBonusPercents($payment);
        $terminalId = config('api-clients.fk.terminal_id');
        $terminalSecret1 = config('api-clients.fk.terminal_secret_1');
        $sign = md5($terminalId . ':' . $amount . ':' . $terminalSecret1 . ':RUB:' . $payment->id);

        $data = [
            'm' => $terminalId,
            'oa' => $amount,
            'o' => $payment->id,
            'currency' => 'RUB',
            's' => $sign,
            'i' => 1
        ];

        return new PaymentRedirectResult(
            self::ACTION_REDIRECT,
            "https://pay.fk.money/?" . http_build_query($data),
        );
    }

    public function withdraw(Withdraw $withdraw): WithdrawResult
    {
        if ($withdraw->system !== 'fk') {
            return new WithdrawResult(false, WithdrawStatusEnum::DECLINE);
        }

        try {
            Log::debug('FK Withdraw', ['withdraw' => $withdraw->toArray()]);
            $transferId = $this->client->transfer($withdraw->wallet, (int)$withdraw->sumWithCom, (string)$withdraw->id);
        } catch (\Exception $e) {
            Log::error('FK Withdraw error', ['error' => $e->getMessage()]);
            return new WithdrawResult(false, WithdrawStatusEnum::DECLINE, $e->getMessage());
        }

        return new WithdrawResult(true, WithdrawStatusEnum::SUCCESS);
    }

    public function isAutoWithdrawAvailable(Withdraw $withdraw): bool
    {
        $successWitdraws = Withdraw::where('user_id', $withdraw->user_id)
            ->where('status', 1)
            ->count();

        return ($successWitdraws >= 3 && empty($withdraw->user->auto_withdraw));
    }

    public function getBalance(): array
    {
        return $this->client->balance() ?? [];
    }
}