<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TournamentService;
use Illuminate\Support\Facades\Log;

final class CheckTournament extends Command
{
    protected $signature = 'tournament:check';
    protected $description = 'Check and finish active tournament if needed, and start a new one.';

    public function handle(): int
    {
        $tournamentService = app(TournamentService::class);

        if ($tournamentService->finishActiveTournamentIfNeeded()) {
            $this->info('Tournament finished and new tournament started.');
        } else {
            $this->info('No tournament to finish.');
        }

        Log::debug('Tournaments checked');

        return self::SUCCESS;
    }
}
