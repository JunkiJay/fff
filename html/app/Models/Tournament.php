<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property mixed|null $history_leaders
 * @property string|null $status
 * @property string $end_at
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Tournament extends Model
{
    protected $fillable = [
        'history_leaders',
        'end_at',
        'status'
    ];

    protected $casts = [
        'history_leaders' => 'json',
        'end_at' => 'datetime'
    ];
}
