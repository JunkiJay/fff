<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $days
 * @property string|null $places
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class SettingTournament extends Model
{
    protected $fillable = [
        'places',
        'days'
    ];

    protected $casts = [
        'places' => 'array',
    ];
}
