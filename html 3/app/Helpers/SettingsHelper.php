<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;

class SettingsHelper
{
    private static Setting|null $settings = null;

    public static function getSettings(): Setting
    {
        if (self::$settings === null) {
            self::$settings = Setting::first();
        }

        if (self::$settings === null) {
            throw new \Exception('Не найдены настройки');
        }

        return self::$settings;
    }
}