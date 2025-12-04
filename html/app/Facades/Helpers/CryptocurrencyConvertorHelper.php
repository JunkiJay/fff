<?php

namespace App\Facades\Helpers;

use App\Services\Currencies\Enums\CurrenciesEnum;

class CryptocurrencyConvertorHelper
{
    public static function convertRubToUsdt($amount): float|int
    {
        $url = "https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=rub";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['tether']['rub'])) {
            $rubToUsdtRate = $data['tether']['rub'];
            return $amount / $rubToUsdtRate;
        }

        throw new \Exception('coingecko did not respond');
    }

    public static function getRate(CurrenciesEnum $from, CurrenciesEnum $to): float|int
    {
        $url = "https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=rub";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['tether']['rub'])) {
            $rubToUsdtRate = $data['tether']['rub'];

            if ($from === CurrenciesEnum::RUB) {
                return 1 / $rubToUsdtRate;
            } elseif ($to === CurrenciesEnum::RUB) {
                return $rubToUsdtRate;
            }

            return $rubToUsdtRate;
        }

        throw new \Exception('cannot get rate');
    }

    public static function convertUsdtToRub($amount)
    {
        $url = "https://api.coingecko.com/api/v3/simple/price?ids=tether&vs_currencies=rub";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['tether']['rub'])) {
            $price = $data['tether']['rub'];
            return $amount * $price;
        }

        throw new \Exception('coingecko did not respond');
    }
}