<?php

declare(strict_types=1);

namespace App\Api\OnePlat\ValueObjects;

readonly class OnePlatSBPNote
{
    public function __construct(
        public string $pan,
        public string $bank,
        public string $fio,
    ) {
    }
}