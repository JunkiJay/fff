<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $user_id
 * @property float $amount
 * @property int $bombs
 * @property int $step
 * @property mixed|null $grid
 * @property int $status
 * @property int $fake
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Mine extends Model
{
    protected $fillable = ['user_id', 'amount', 'bombs', 'step', 'grid', 'status', 'fake'];
}
