<?php

declare(strict_types=1);

namespace App\Services\Payments\DTO;

readonly class UserPaymentsCountsDTO
{
    /**
     * @param array<int, int> $paymentsCounts
     */
    public function __construct(
        public int $userId,
        public array $paymentsCounts
    ) {
    }
}