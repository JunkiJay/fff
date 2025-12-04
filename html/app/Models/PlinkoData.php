<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property mixed $data
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class PlinkoData extends Model
{
    protected $guarded = [];
    protected $casts = ['data' => 'array'];
}
