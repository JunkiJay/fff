<?php

declare(strict_types=1);

namespace App\Console\Commands\Slots;

use App\Models\MobuleSlot;
use App\Models\SlotSession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PopularSlotsOrderCommand extends Command
{
    protected $signature = 'slots:popular-order';

    public function handle(): void
    {
        $slots = SlotSession::query()
            ->selectRaw('slot_sessions.game_id, count(1) as game_sessions_count')
            ->join('mobule_slots', 'slot_sessions.game_id', '=', 'mobule_slots.id')
            ->groupBy('slot_sessions.game_id')
            ->limit(18)
            ->where('slot_sessions.created_at', '>=', now()->subDays(1))
            ->orderBy('game_sessions_count', 'desc')
            ->pluck('game_id');


        MobuleSlot::query()->whereNotNull('order')->update(['order' => null]);
        $position = 1;
        foreach ($slots as $gameId) {
            MobuleSlot::query()->where('id', $gameId)->update(['order' => $position]);
            $position++;
        }

        Log::debug('Slot orders updated');
    }
}