<?php

declare(strict_types=1);

namespace App\Services\Currencies;

use App\Services\Currencies\Contracts\CurrencyConverterInterface;
use App\Services\Currencies\Converters\USDTCurrencyConverter;
use App\Services\Currencies\Enums\CurrenciesEnum;

class CurrenciesConverter
{
    public function convert(CurrenciesEnum $from, CurrenciesEnum $to, int $amount): int|float|null
    {
        if ($from === $to) {
            if ($from === CurrenciesEnum::RUB) {
                return $amount;
            } else {
                throw new \Exception("Try to convert $from->value to $to->value, but base currency is RUB, maybe u have a bug.");
            }
        }

        $converter = $this->resolveConverter($from !== CurrenciesEnum::RUB ? $from : $to);

        return $converter->convert($from, $to, $amount);
    }

    private function resolveConverter(CurrenciesEnum $currency): CurrencyConverterInterface
    {
        if ($currency === CurrenciesEnum::USDT) {
            return app(USDTCurrencyConverter::class);
        }

        throw new \DomainException("Currency $currency->value not supported.");
    }
}