<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int|null $id
 * @property string $name
 * @property float $sum
 * @property int $activation
 * @property float $wager
 * @property string $type
 * @property string|null $end_time
 * @property int|null $quantity_spin
 * @property int|null $id_spin
 * @property float|null $min_deposits
 * @property int|null $deposits_days
 * @property int $only_private_club
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Promocode extends Model
{
    protected $fillable = [
        'name',
        'sum',
        'activation',
        'wager',
        'type',
        'end_time',
        'quantity_spin',
        'id_spin',
        'min_deposits',
        'deposits_days',
        'only_private_club',
    ];
}