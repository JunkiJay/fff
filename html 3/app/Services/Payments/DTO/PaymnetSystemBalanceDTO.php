<?php

declare(strict_types=1);

namespace App\Services\Payments\DTO;

use App\Enums\Currencies\CurrencyEnum;
use App\Enums\Payments\PaymentSystemEnum;

readonly class PaymnetSystemBalanceDTO
{
    public function __construct(
        public PaymentSystemEnum $paymentSystem,
        public int|float|string $balance,
        public CurrencyEnum $currency
    ) {
    }
}