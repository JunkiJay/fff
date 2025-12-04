<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $user_id
 * @property int $game_id
 * @property string $created_at
 * @property string $updated_at
 */
class SlotSession extends Model
{

    protected $table = 'slot_sessions';
    protected $fillable = [
        'id',
        'user_id',
        'game_id',
        'created_at'
    ];
}
