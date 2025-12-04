<?php

declare(strict_types=1);

namespace App\Services\Currencies\Facades;

use App\Services\Currencies\CurrenciesConverter;
use Illuminate\Support\Facades\Facade;

class CurrencyConverterFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return CurrenciesConverter::class;
    }
}