<?php

declare(strict_types=1);

namespace App\Api\FK\Entities;

readonly class FKHistory
{
    public function __construct(
        public int $id,
        public int $currency,
        public string $currency_code,
        public int $amount,
        public string $date,
        public string $operation,
        public string $operationTitle,
    ) {
    }
}