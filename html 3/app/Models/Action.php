<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $user_id
 * @property string $action
 * @property float $balanceBefore
 * @property float $balanceAfter
 * @property string $created_at
 * @property string|null $updated_at
 */
class Action extends Model
{
    protected $guarded = [];
}
