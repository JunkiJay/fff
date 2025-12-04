<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $show
 * @property string $gr_title
 * @property int $gr_id
 * @property int $gm_is_board
 * @property int $gm_m_w
 * @property int $gm_ln
 * @property int $gm_is_copy
 * @property string $gm_url
 * @property int $gm_is_retro
 * @property int $gm_bk_id
 * @property int $gm_d_w
 * @property string $icon_url
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class B2bSlot extends Model
{
    protected $guarded = [];
}
