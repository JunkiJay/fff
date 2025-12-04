<?php

declare(strict_types=1);

namespace App\Services\Currencies\Converters;

use App\Api\Cryptobot\CryptobotApiClient;
use App\Helpers\CryptocurrencyConvertorHelper;
use App\Services\Currencies\Contracts\CurrencyConverterInterface;
use App\Services\Currencies\Enums\CurrenciesEnum;

use Illuminate\Support\Facades\Cache;

use function Symfony\Component\String\s;

class USDTCurrencyConverter implements CurrencyConverterInterface
{
    public static $rate = null;


    public function __construct(private CryptobotApiClient $cryptobotApiClient)
    {
    }

    public function convert(CurrenciesEnum $from, CurrenciesEnum $to, int $amount): float|int|null
    {
        if ($this->getRate($from, $to) === null) {
            return null;
        }

        return $amount * $this->getRate($from, $to);
    }

    public function getRate(CurrenciesEnum $from, CurrenciesEnum $to)
    {
        if (self::$rate !== null) {
            return self::$rate;
        }

        $rate = null;

        foreach (['cryptobot', 'coingecko'] as $item) {
            try {
                $rate = Cache::remember('usdt_rate' . $item, 60, function () use ($item, $from, $to) {
                    return match ($item) {
                        'cryptobot' => $this->cryptobotApiClient->getExchangeRates()->getRate($from, $to),
                        'coingecko' => CryptocurrencyConvertorHelper::getRate($from, $to),
                    };
                });
            } catch (\Throwable $e) {
                \Log::error('Convertion failed', [$e->getMessage()]);
                continue;
            }
        }

        self::$rate = $rate;

        return self::$rate;
    }
}