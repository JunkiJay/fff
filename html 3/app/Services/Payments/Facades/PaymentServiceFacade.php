<?php

declare(strict_types=1);

namespace App\Services\Payments\Facades;

use App\Services\Payments\PaymentsService;
use Illuminate\Support\Facades\Facade;

class PaymentServiceFacade extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return PaymentsService::class;
    }
}