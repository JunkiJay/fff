<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $user_id
 * @property int $promo_id
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PromocodeActivation extends Model
{
    protected $fillable = [
        'user_id', 'promo_id', 'status'
    ];
}
