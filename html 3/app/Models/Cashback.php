<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property float $amount
 * @property string|null $created_at
 * @property string $updated_at
 */
class Cashback extends Model
{
    protected $table = 'cashback';
    protected $fillable = [
        'user_id', 'amount', 'created_at'
    ];
}
