<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\SettingTournament;
use App\Models\Tournament;
use App\Models\User;
use App\Services\Tournaments\Enums\TournamentEnum;
use App\Services\Tournaments\TournamentsService;
use Illuminate\Support\Facades\DB;

class TournamentController extends Controller
{
    protected $settings;
    protected $leaders;

    public function __construct()
    {
        $this->settings = SettingTournament::first();
        $this->leaders = Tournament::latest()->first();
    }

    public function leaders()
    {
        return response()->json([
            'leaders' => $this->leaders->history_leaders,
            'places' => $this->settings->places
        ]);
    }

    public function timer()
    {
        return response()->json([
            'endDate' => $this->leaders->end_at,
        ]);
    }

    public function live(TournamentsService $service)
    {
        return response()->json([
            'success' => true,
            'finished' => false,
            'leaders' => $service->getCurrentSummary(TournamentEnum::MONEY_FLOW),
            'places' => $this->settings->places
        ], 200);
    }
    /**
     * Начисляет выигрыши победителям турнира.
     * @param array $winners Массив вида [['id' => user_id, 'reward' => сумма], ...]
     */
    private function payWinners(array $winners)
    {
        DB::beginTransaction();
        try {
            foreach ($winners as $winner) {
                \Log::info('Paying winner:', $winner);
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
                        \Log::info("User #{$user->id} new balance after payout: {$user->fresh()->balance}");
                    } else {
                        \Log::warning("User not found for tournament payout", $winner);
                    }
                } else {
                    \Log::warning("Invalid winner data", $winner);
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Tournament payout error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

