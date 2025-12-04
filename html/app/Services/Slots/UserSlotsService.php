<?php

declare(strict_types=1);

namespace App\Services\Slots;

use App\Models\SlotSession;
use Illuminate\Support\Facades\DB;

class UserSlotsService
{
    public function last(int $userId, int $counts = 6): array
    {
        $slots = SlotSession::query()
            ->select(
                [
                    'slots.*',
                    DB::raw('MAX(slot_sessions.created_at) as max_created_at'),
                ]
            )
            ->where('slot_sessions.user_id', $userId)
            ->groupBy('game_id')
            ->rightJoin('slots', 'slot_sessions.game_id', '=', 'slots.id')
            ->orderBy('max_created_at', 'desc')
            ->limit($counts)
            ->get();

        return $slots->toArray();
    }
}