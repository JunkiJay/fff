<?php

declare(strict_types=1);

namespace App\Api\Gotham\Requests;

use App\Services\Currencies\Enums\CurrenciesEnum;

readonly class GothamCreatePaymentRequest
{
    public function __construct(
        public int $amount,
        public CurrenciesEnum $currency,
        public string $externalId,
        public string $callbackSecret,
    ) {
    }
}