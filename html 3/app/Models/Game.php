<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $user_id
 * @property string $game
 * @property float $bet
 * @property float $chance
 * @property float $win
 * @property string|null $type
 * @property int $fake
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Game extends Model
{
    protected $fillable = [
        'user_id', 'game', 'bet', 'chance', 'win', 'type', 'dice', 'mine', 'fake'
    ];
}
