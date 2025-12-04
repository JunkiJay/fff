<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Action;
use App\Models\SettingTournament;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class TournamentService
{
    private $settings;

    public function __construct()
    {
        $this->settings = SettingTournament::first();
    }

    /**
     * Проверяет и завершает активный турнир, если пора, и запускает новый.
     * Возвращает true если был завершён и запущен новый турнир.
     */
    public function finishActiveTournamentIfNeeded()
    {
        $now = Carbon::now();
        $tournament = Tournament::where('status', 'active')->latest()->first();
        if (!$tournament) {
            // Нет активного турнира — создаём новый
            Tournament::create([
                'history_leaders' => [],
                'status' => 'active',
                'end_at' => Carbon::now()->addWeeks(1),
            ]);
            return true;
        }

        if ($now->lessThanOrEqualTo($tournament->end_at)) {
            return false;
        }

        // Найти предыдущий турнир для периода
        $previousTournament = Tournament::where('end_at', '<', $tournament->end_at)
            ->orderByDesc('end_at')
            ->first();
        $startOfPeriod = $previousTournament ? Carbon::parse($previousTournament->end_at) : Carbon::parse($tournament->start_at)->startOfDay();
        $endOfPeriod = Carbon::parse($tournament->end_at)->endOfDay();

        $actions = Action::query()
            ->where('action', 'LIKE', "%slot%")
            ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
            ->select(['user_id', DB::raw('SUM(ABS(balanceAfter - balanceBefore)) as amount')])
            ->groupBy('user_id')
            ->orderBy('amount', 'desc')
            ->limit(10)
            ->get();

        $leaders = $actions->map(function ($action, $index) {
            $user = User::find($action['user_id']);
            if (!$user) return null;
            return [
                'id' => $user->id,
                'avatar' => $user->avatar,
                'username' => $user->username,
                'position' => $index + 1,
                'amount' => round($action['amount']),
                'reward' => $this->settings->places[$index + 1] ?? 0
            ];
        })->filter()->values()->toArray();

        // Сохраняем историю лидеров и закрываем турнир
        $tournament->history_leaders = $leaders;
        $tournament->status = 'finished';
        $tournament->save();

        $winnersArray = collect($leaders)
            ->map(function($l) { return ['id' => $l['id'], 'reward' => $l['reward']]; })
            ->toArray();
        Log::info('Tournament payout winners array:', $winnersArray);
        $this->payWinners($winnersArray);

        // Создаём новый турнир
        Tournament::create([
            'history_leaders' => [],
            'status' => 'active',
            'end_at' => Carbon::now()->addWeeks(1),
        ]);

        return true;
    }

    /**
     * Начисляет выигрыши победителям турнира.
     * @param array $winners [['id' => user_id, 'reward' => сумма], ...]
     */
    public function payWinners($winners)
    {
        DB::beginTransaction();
        try {
            foreach ($winners as $winner) {
                Log::info('Paying winner:', $winner);
                if (!empty($winner['id']) && !empty($winner['reward']) && $winner['reward'] > 0) {
                    $user = User::find($winner['id']);
                    if ($user) {
                        $reward = (int)$winner['reward'];
                        Action::create([
                            'user_id' => $user->id,
                            'action' => 'Турнир (+' . $reward . ')',
                            'balanceBefore' => $user->balance,
                            'balanceAfter' => $user->balance + $reward
                        ]);
                        $user->increment('balance', $reward);
                        $user->increment('wager', $reward * 3);
                        Log::info("User #{$user->id} new balance after payout: {$user->fresh()->balance}");
                    } else {
                        Log::warning("User not found for tournament payout", $winner);
                    }
                } else {
                    Log::warning("Invalid winner data", $winner);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Tournament payout error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
