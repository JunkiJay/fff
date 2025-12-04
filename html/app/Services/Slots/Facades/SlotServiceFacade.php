<?php

declare(strict_types=1);

namespace App\Services\Slots\Facades;

use App\Services\Slots\SlotsService;
use Illuminate\Support\Facades\Facade;

class SlotServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SlotsService::class;
    }
}