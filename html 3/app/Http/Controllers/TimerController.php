<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class TimerController extends Controller
{
    public function timer()
    {
        return Setting::query()->find(1)->bot_timer;
    }
}
