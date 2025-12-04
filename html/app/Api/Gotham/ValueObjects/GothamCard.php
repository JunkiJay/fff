<?php

declare(strict_types=1);

namespace App\Api\Gotham\ValueObjects;

readonly class GothamCard
{
    public function __construct(
        public string $bank,
        public string $number,
        public string $cardholder,
        public string $trafficType,
    ) {
    }
}