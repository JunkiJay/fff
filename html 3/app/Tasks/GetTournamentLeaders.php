<?php

namespace App\Tasks;

use App\Models\Action;
use App\Models\SettingTournament;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetTournamentLeaders
{
    private $settings;

    public function __construct()
    {
        $this->settings = SettingTournament::first();
    }

    public function run()
    {
        $latest_tournament = Tournament::latest()->first();

        $actions = Action::query()
            ->where('action', 'LIKE', "%slot%")
            ->whereBetween('created_at', [Carbon::parse($latest_tournament->end_at)->subWeek(), $latest_tournament->end_at])
            ->select(['user_id', DB::raw('SUM(ABS(balanceAfter - balanceBefore)) as amount')])
            ->groupBy('user_id')
            ->orderBy('amount', 'desc')
            ->limit(10)
            ->get();

        $leaders = $actions->map(function ($action, $index) {
            $user = User::find($action['user_id']);

            return [
                'id' => $user->id,
                'avatar' => $user->avatar,
                'username' => $user->username,
                'position' => $index + 1,
                'amount' => $action['amount'],
                'reward' => $this->settings->places[$index + 1] ?? 0
            ];

            Action::create([
                'user_id' => $user->id,
                'action' => 'Турнир (+' . $credit_amount . ')',
                'balanceBefore' => $user->balance,
                'balanceAfter' => $user->balance + $this->settings->places[$index + 1]
            ]);
        });

        return $leaders;
    }
}
