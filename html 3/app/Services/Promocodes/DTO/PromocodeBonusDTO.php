<?php

declare(strict_types=1);

namespace App\Services\Promocodes\DTO;

readonly class PromocodeBonusDTO
{
    public function __construct(public int $bonus, public int $wager)
    {
    }
}