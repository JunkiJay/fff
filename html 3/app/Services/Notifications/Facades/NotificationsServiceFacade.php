<?php

declare(strict_types=1);

namespace App\Services\Notifications\Facades;

use App\Services\Notifications\NotificationsService;
use Illuminate\Support\Facades\Facade;

class NotificationsServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return NotificationsService::class;
    }
}