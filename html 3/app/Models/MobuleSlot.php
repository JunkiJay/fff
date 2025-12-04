<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $show
 * @property string $alias
 * @property string $group_alias
 * @property string $title
 * @property string $provider
 * @property int $is_enabled
 * @property int $is_freerounds_enabled
 * @property int $desktop_enabled
 * @property int $mobile_enabled
 * @property int $base_total_bet
 * @property int $max_bet_level
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class MobuleSlot extends Model
{
    protected $guarded = [];
}
