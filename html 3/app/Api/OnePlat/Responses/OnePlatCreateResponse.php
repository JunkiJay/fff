<?php

declare(strict_types=1);

namespace App\Api\OnePlat\Responses;

use App\Api\OnePlat\ValueObjects\OnePlatSBPPayment;
use FKS\Serializer\SerializableObject;

class OnePlatCreateResponse extends SerializableObject
{
    public function __construct(
        public OnePlatSBPPayment $payment,
        public string $url,
        public string $guid,
    ) {
    }
}