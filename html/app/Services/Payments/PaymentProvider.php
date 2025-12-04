<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\Payment;
use App\Models\Withdraw;
use App\Services\Payments\DTO\PaymnetSystemBalanceDTO;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use App\Services\Payments\ValueObjects\PaymentSuccessResult;
use App\Services\Payments\ValueObjects\WithdrawResult;

abstract class PaymentProvider
{
    public const string ACTION_REDIRECT = 'redirect';
    public const string ACTION_SHOW_SBP_FORM = 'show_sbp_form';
    public const string ACTION_SHOW_ERROR = 'show_error';

    public function __construct(public readonly PaymentProviderConfig $config) {}

    public function isAutoWithdrawAvailable(Withdraw $withdraw): bool
    {
        return false;
    }

    public function withdraw(Withdraw $withdraw): WithdrawResult
    {
        throw new \Exception('Provider do not implement withdraw');
    }

    public function pay(Payment $payment): PaymentRedirectResult|PaymentShowSBPResult|PaymentErrorResult
    {
        throw new \Exception('Provider do not implement deposit');
    }

    /**
     * @return PaymnetSystemBalanceDTO[]
     */
    public function getBalance(): array
    {
        throw new \Exception('Provider do not implement getBalance');
    }

    public function handleCreateCallback(Payment $payment, array $data): PaymentSuccessResult|PaymentErrorResult
    {
        throw new \Exception('Provider do not implement handleCreateCallback');
    }

    public function reduceAmountByBonusPercents(Payment $payment): float|int
    {
        $amount = $payment->sum;
        $bonusPercents = $this->config->getPaymentMethodConfig($payment->method)->bonusPercent;

        if ($bonusPercents) {
            $amount = $payment->sum - ($payment->sum / (100 + $bonusPercents) * $bonusPercents);
        }

        return $amount;
    }
}