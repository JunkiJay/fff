<?php

namespace App\Api\OnePlatS\ValueObjects;

readonly class OnePlatSBPPayment
{
    public function __construct(
        public OnePlatSBPNote $note,
        public int $amountToPay
    ) {
    }
}