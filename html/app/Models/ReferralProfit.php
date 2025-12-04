<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int|null $id
 * @property int $from_id
 * @property int $ref_id
 * @property float $amount
 * @property int $level
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class ReferralProfit extends Authenticatable
{
    use Notifiable;

    protected $guarded = [];
}
