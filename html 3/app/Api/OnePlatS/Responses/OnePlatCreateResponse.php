<?php

declare(strict_types=1);

namespace App\Api\OnePlatS\Responses;

use App\Api\OnePlatS\ValueObjects\OnePlatSBPPayment;

readonly class OnePlatCreateResponse
{
    public function __construct(
        public OnePlatSBPPayment $payment,
        public string $url
    ) {
    }
}