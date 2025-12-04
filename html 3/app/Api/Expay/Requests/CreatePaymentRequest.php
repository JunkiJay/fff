<?php

declare(strict_types=1);

namespace App\Api\Expay\Requests;

readonly class CreatePaymentRequest
{
    public function __construct(
        public int|float $amount,
        public string $clientTransactionId,
        public string $fingerprint,
    ) {
    }
}