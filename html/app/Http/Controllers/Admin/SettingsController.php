<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SettingTournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SettingsController extends Controller
{
    public function index(Request $r)
    {
        if($r->user()->admin_role == 'moder'){
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }
        $settings_tournament = SettingTournament::first();
        return view('admin.settings.index', compact('settings_tournament'));
    }

    public function save(Request $request)
    {
        $tournament = $request->get('tournament');
        SettingTournament::query()->find(1)->update($tournament);

        if ($this->config->bot_timer !== $request->get('bot_timer')) {
            Redis::publish('setNewBotTimer', $request->get('bot_timer'));
        }

        Setting::query()->find(1)->update($request->except('tournament'));
        return redirect()->back()->with('success', 'Настройки сохранены!');
    }
}
