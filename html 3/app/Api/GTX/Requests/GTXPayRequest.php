<?php

declare(strict_types=1);

namespace App\Api\GTX\Requests;

use App\Services\Payments\Enum\PaymentMethodEnum;

class GTXPayRequest
{
    public function __construct(
        public int $amount,
        public string $orderId,
        public string $userId,
        public PaymentMethodEnum $method
    ) {
    }
}