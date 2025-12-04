<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Validator::extend(
            'withdraw_provider',
            static function ($attribute, $value, $parameters, $validator) {
                dd($validator);
            },
            'Выбранный метод недоступен'
        );
        Validator::extend(
            'payment_provider',
            static function ($attribute, $value, $parameters, $validator) {
                dd($validator);
            },
            'Выбранный метод недоступен'
        );
    }
}