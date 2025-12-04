<?php

namespace App\Api\OnePlat\ValueObjects;

use FKS\Serializer\SerializableObject;

class OnePlatSBPPayment extends SerializableObject
{
    public function __construct(
        public OnePlatSBPNote $note,
        public int $amountToPay
    ) {
    }
}