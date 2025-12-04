<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $user_id
 * @property int $slot_id
 * @property int $trx_id
 * @property string|null $type
 * @property int $amount
 * @property float|null $balanceBefore
 * @property float|null $balanceAfter
 * @property string $created_at
 * @property string|null $updated_at
 */
class SlotsData extends Model
{
    protected $guarded = [];
}
