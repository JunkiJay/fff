<?php

namespace App\Services\Payments\DTO;

use App\Models\User;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\PaymentProvidersEnum;

readonly class CreateWithdrawDTO
{
    public function __construct(
        public float $amount,
        public string $wallet,
        public PaymentProvidersEnum $provider,
        public PaymentMethodEnum $method,
        public User $user,
        public ?string $variant = null,
    ) {
    }
}