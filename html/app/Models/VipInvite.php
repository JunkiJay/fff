<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $user_id
 * @property string $invite_link
 * @property string $created_at
 * @property string $used_at
 * @property int $is_active
 */
final class VipInvite extends Model
{
    protected $table = 'vip_invites';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'invite_link',
        'created_at',
        'used_at',
        'is_active',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'is_active' => 'integer',
        'created_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}
