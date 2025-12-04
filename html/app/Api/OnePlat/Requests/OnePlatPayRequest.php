<?php

declare(strict_types=1);

namespace App\Api\OnePlat\Requests;

readonly class OnePlatPayRequest
{
    public function __construct(
        public int $paymentId,
        public int $userId,
        public int|float $amount,
    ) {
    }
}