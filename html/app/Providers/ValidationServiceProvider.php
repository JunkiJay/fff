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
                // Валидация провайдера для вывода
                return true; // Пока всегда true, можно добавить логику позже
            },
            'Выбранный метод недоступен'
        );
        Validator::extend(
            'payment_provider',
            static function ($attribute, $value, $parameters, $validator) {
                // Валидация провайдера для платежа
                return true; // Пока всегда true, можно добавить логику позже
            },
            'Выбранный метод недоступен'
        );
        Validator::extend(
            'payment_method',
            static function ($attribute, $value, $parameters, $validator) {
                // Валидация метода оплаты (аналог payment_provider для обратной совместимости)
                return true; // Пока всегда true, можно добавить логику позже
            },
            'Выберите способ оплаты'
        );
    }
}