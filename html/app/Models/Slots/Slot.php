<?php

namespace App\Models\Slots;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property string $title
 * @property string $provider
 * @property string $image
 * @property string $alias
 * @property int|null $priority
 * @property int|null $show
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Slot extends Model
{
    public $timestamps = true;
    protected $guarded = [];
}
