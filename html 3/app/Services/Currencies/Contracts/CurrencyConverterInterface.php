<?php

namespace App\Services\Currencies\Contracts;

use App\Services\Currencies\Enums\CurrenciesEnum;

interface CurrencyConverterInterface
{
    public function convert(CurrenciesEnum $from, CurrenciesEnum $to, int $amount): float|int|null;
}