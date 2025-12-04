<?php

declare(strict_types=1);

namespace App\Services\Payments\Collections;

use App\Enums\Currencies\CurrencyEnum;
use Illuminate\Support\Collection;

class PaymentSystemBalanceCollection extends Collection
{
    public function getBalance(CurrencyEnum $currency): int|float|string|null
    {
        return $this->where('currency', $currency)->first()?->balance;
    }
}