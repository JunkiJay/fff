<?php

declare(strict_types=1);

namespace App\Api\OnePayment\Responses;

use App\Api\OnePayment\ValueObjects\OnePaymentsBalanceAttributes;

readonly class OnePaymentsBalanceResponse
{
    public function __construct(
        public string $id,
        public string $balance,
        public OnePaymentsBalanceAttributes $attributes,
    ) {
    }
}