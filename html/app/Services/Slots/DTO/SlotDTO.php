<?php

declare(strict_types=1);

namespace App\Services\Slots\DTO;

use App\Services\Slots\Enums\SlotSourceEnum;

readonly class SlotDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public SlotSourceEnum $source,
        public string $imageUrl,
    ) {
    }
}