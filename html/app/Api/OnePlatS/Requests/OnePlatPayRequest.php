<?php

declare(strict_types=1);

namespace App\Api\OnePlatS\Requests;

use App\Models\User;

readonly class OnePlatPayRequest
{
    public function __construct(
        public int $paymentId,
        public int $userId,
        public int|float $amount,
    ) {
    }
}