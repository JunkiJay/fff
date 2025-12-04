<?php

namespace App\Console\Commands;

use App\Models\SettingTournament;
use App\Models\Tournament;
use App\Models\User;
use App\Tasks\GetTournamentLeaders;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FinishTournament extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tournament:end';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tournament End';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Задача запускаеться!');
        return DB::transaction(function () {
            $settings = SettingTournament::first();
            $latet_tournament = Tournament::latest()->first();
            $this->info('В процессе 0');
            if (now() < $latet_tournament->end_at->addDays($settings->days)) {
                return 0;
            }

            $leaders = (new GetTournamentLeaders())->run();
            if (empty($leaders)) {
                return 0;
            }
            $this->info('В процессе 1:');
            $leaders->filter(function ($leader) {
                return $leader['reward'] > 0;
            })->each(function ($leader) {
                $user = User::where('id', $leader['id'])->lockForUpdate()->first();
                $user->increment('wager', $leader['reward'] * 3);
                $user->actions()->create([
                    'action' => 'tournament (+' . $leader['reward'] . ')',
                    'balanceBefore' => $user->balance - $leader['reward'],
                    'balanceAfter' => $user->balance
                ]);
            });
            $this->info('В процессе 2');
            $history_leaders = $leaders->slice(0, 10)->map(function ($leader) {
                return [
                    'user_id' => $leader['id'],
                    'username' => $leader['username'],
                    'position' => $leader['position'],
                    'amount' => $leader['amount'],
                    'reward' => $leader['reward']
                ];
            })->values();

            Tournament::create([
                'end_at' => $latet_tournament->end_at->addDays($settings->days),
                'history_leaders' => $history_leaders
            ]);
            $this->info('В процессе 4');
            $this->info('Задача выполнена!');
        });
    }
}
