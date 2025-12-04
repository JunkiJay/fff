<?php

declare(strict_types=1);

namespace App\Services\Tournaments;

use App\Models\Action;
use App\Models\SettingTournament;
use App\Models\Tournament;
use App\Models\User;
use App\Repositories\Tournaments\TournamentRepository;
use App\Services\Tournaments\Enums\TournamentEnum;
use Carbon\Carbon;
use FKS\Search\ValueObjects\SearchConditions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

readonly class TournamentsService
{
    public function __construct(private TournamentRepository $repository) {}

    public function list(SearchConditions $conditions): \FKS\Search\Collections\EntitiesCollection|\Illuminate\Contracts\Database\Eloquent\Builder|bool|\Illuminate\Support\Collection|null
    {
        return $this->repository->search($conditions);
    }

    public function getCurrentSummary(TournamentEnum $tournamentEnum)
    {
        $tournament = Tournament::where('status', 'active')->latest()->first();

        if (!$tournament) {
            throw new \Exception('Турнир не найден', 404);
        }

        return $this->getSummary($tournament);
    }

    public function getSummary(Tournament $tournament)
    {
        $cacheKey = 'tournament_summary_' . $tournament->id;

        return Cache::remember($cacheKey, 60, function () use ($tournament) {
            $previousTournament = Tournament::where('end_at', '<', $tournament->end_at)
                ->orderByDesc('end_at')
                ->first();
            $startOfPeriod = $previousTournament ? Carbon::parse($previousTournament->end_at) : Carbon::parse($tournament->start_at)->startOfDay();
            $endOfPeriod = Carbon::parse($tournament->end_at)->endOfDay();

            $actions = Action::query()
                ->where('action', 'LIKE', 'slot (-%')
                ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                ->select(['user_id', DB::raw('SUM(balanceBefore - balanceAfter) as amount')])
                ->groupBy('user_id')
                ->orderBy('amount', 'desc')
                ->limit(10)
                ->get();

            return $actions->map(function ($action, $index) {
                $user = User::find($action['user_id']);
                if (!$user) return null;
                return [
                    'id' => $user->id,
                    'avatar' => $user->avatar,
                    'username' => $user->username,
                    'position' => $index + 1,
                    'amount' => round($action['amount']),
                    'reward' => SettingTournament::first()->places[$index + 1] ?? 0
                ];
            })->filter()->values()->toArray();
        });
    }
}