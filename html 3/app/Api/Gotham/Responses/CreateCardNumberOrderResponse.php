<?php

declare(strict_types=1);

namespace App\Api\Gotham\Responses;

use App\Api\Gotham\ValueObjects\GothamCard;

readonly class CreateCardNumberOrderResponse
{
    public function __construct(
        public string $id,
        public int $amount,
        public string $createdAt,
        public string $externalId,
        public float $currentUsdtPrice,
        public GothamCard $card,
    ) {
    }
}