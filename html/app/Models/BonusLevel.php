<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property string|null $title
 * @property int|null $goal
 * @property float $reward
 * @property string|null $background
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class BonusLevel extends Model
{
    protected $guarded = [];
}
