<?php

declare(strict_types=1);

namespace App\Api\OnePayment\Requests;

use App\Services\Currencies\Enums\CurrenciesEnum;

class OnePaymentPayRequest
{
    public function __construct(
        public CurrenciesEnum $nationalCurrency,
        public int $nationalCurrencyAmount,
        public string $externalOrderId,
        public string $callbackUrl,
        public string $clientMerchantId,
    ) {
    }
}