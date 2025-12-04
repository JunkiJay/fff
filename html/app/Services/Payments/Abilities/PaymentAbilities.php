<?php

declare(strict_types=1);

namespace App\Services\Payments\Abilities;

use FKS\Abilities\Contracts\AbilitiesResolverInterface;

class PaymentAbilities implements AbilitiesResolverInterface
{
    public function canSeeAllPayments(): bool
    {
        return auth()->user()->is_admin === 1;
    }
}