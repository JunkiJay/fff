<?php

declare(strict_types=1);

namespace App\Api\Paradise\Requests;

use App\Models\User;

readonly class ParadisePayRequest
{
    public function __construct(
        public int $paymentId,
        public int $userId,
        public int|float $amount,
        public string $ip,
    ) {
    }
}