<?php

declare(strict_types=1);

namespace App\Services\Actions\DTO;

readonly class ActionCreateDTO
{
    public function __construct(
        public int $userId,
        public string $action,
        public int|float $balanceBefore,
        public int|float $balanceAfter,
        public ?string $additionalText = null,
    ) {
    }
}