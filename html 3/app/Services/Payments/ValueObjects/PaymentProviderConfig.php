<?php

declare(strict_types=1);

namespace App\Services\Payments\ValueObjects;

use App\Services\Currencies\Enums\CurrenciesEnum;
use App\Services\Payments\Collections\PaymentMethodCollection;
use App\Services\Payments\Collections\WithdrawalMethodCollection;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\PaymentProvidersEnum;
use App\Services\Payments\PaymentProvider;

readonly class PaymentProviderConfig
{
    /**
     * @param class-string<PaymentProvider> $class
     */
    public function __construct(
        public string $class,
        public PaymentProvidersEnum $provider,
        public CurrenciesEnum $baseCurrency,
        public ?PaymentMethodCollection $payment,
        public ?WithdrawalMethodCollection $withdraw,
    ) {
    }

    public function acceptWithdraw(): bool
    {
        return !empty($this->withdraw);
    }

    public function acceptPayment(): bool
    {
        return !empty($this->payment);
    }

    public function getPaymentMethods(): array
    {
        return $this->payment->toArray();
    }

    public function getWithdrawMethods(): array
    {
        return $this->withdraw->toArray();
    }

    public function getPaymentMethodConfig(PaymentMethodEnum  $paymentMethod): ?PaymentMethodConfig
    {
        if (!$this->acceptPayment()) {
            return null;
        }

        return collect($this->payment)->where('method', $paymentMethod)->first();
    }

    public function getWithdrawMethodConfig(PaymentMethodEnum $paymentMethod): ?WithdrawalMethodConfig
    {
        if (!$this->acceptWithdraw()) {
            return null;
        }

        return collect($this->withdraw)->where('method', $paymentMethod)->first();
    }
}