<?php

declare(strict_types=1);

namespace App\Api\Expay\Responses;

readonly class ExpayCreatePaymentResponse
{
    public function __construct(
        public string $alterRefer,
        public string $trackerId,
    ) {
    }
}
