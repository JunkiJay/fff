<?php

declare(strict_types=1);

namespace App\Services\Cards\Helpers;

class RussianCardHelper
{
    public static function isSberbankCard(string|int $cardNumber): bool
    {
        $sberbankBins = [
            '427600',
            '427601',
            '427602',
            '427901',
            '427902',
            '427603',
            '427904',
            '427910',
            '427911',
            '427918',
            '427922',
            '427928',
            '427930',
            '427931',
            '427938',
            '427939',
            '427940',
            '427941',
            '427640',

            // Mastercard
            '546901',
            '546902',
            '546903',
            '546930',
            '546904',
            '546905',
            '546906',
            '546907',
            '546909',
            '546910',
            '546911',
            '546912',
            '546913',
            '546914',
            '546915',
            '546916',
            '546917',

            // MIR
            '220220',
            '220221',
            '220225',
            '220222',
            '220226',
            '220227',
            '220228',
            '220229'
        ];

        // Проверяем, что карта передана как строка и длина её не меньше 6 символов
        if (is_string($cardNumber) && strlen($cardNumber) >= 6) {
            // Получаем первые 6 цифр карты
            $bin = substr($cardNumber, 0, 6);
            // Проверяем, есть ли BIN в списке
            return in_array($bin, $sberbankBins);
        }

        return false;
    }

}