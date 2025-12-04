<?php

declare(strict_types=1);

namespace App\Api\Cryptobot\Responses;

use App\Services\Currencies\Enums\CurrenciesEnum;

readonly class CryptobotExchangeRates
{
    public function __construct(public array $rates)
    {
    }

    public function getRate(CurrenciesEnum $from, CurrenciesEnum $to): int|float|null
    {
        return collect($this->rates)
            ->where([
                'source' => $from->value,
                'target' => $to->value,
            ])
            ->first()['rate'] ?? null;
    }
}