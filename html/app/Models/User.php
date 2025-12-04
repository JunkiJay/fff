<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int|null $id
 * @property string $username
 * @property string|null $email
 * @property float $balance
 * @property string|null $password
 * @property int $auto_payment
 * @property string|null $reset_token
 * @property int|null $freespins
 * @property int|null $fs_slot
 * @property int|null $fs_amount
 * @property string|null $freeround_id
 * @property float|null $cashback
 * @property string|null $promo_limit
 * @property string|null $promo_limit2
 * @property string|null $fs_promo_limit
 * @property string|null $fs_promo_limit2
 * @property float|null $bonus_bank
 * @property int $repost
 * @property float $bonus_balance
 * @property float $wager
 * @property float $slots_wager
 * @property int $wager_status
 * @property string|null $avatar
 * @property int|null $vk_id
 * @property string $tg_id
 * @property string|null $vk_username
 * @property float $dice
 * @property float $mines
 * @property float $bubbles
 * @property float $wheel
 * @property float|null $slots
 * @property float $plinko
 * @property int $total_reposts
 * @property int $is_bot
 * @property int $is_admin
 * @property int $is_youtuber
 * @property int $is_worker
 * @property string|null $admin_role
 * @property int|null $referral_use
 * @property int $referral_send
 * @property float $referral_balance
 * @property float $ref_1_lvl
 * @property float $ref_2_lvl
 * @property float $ref_3_lvl
 * @property int $ban
 * @property int $limit_payment
 * @property string|null $auth_token
 * @property string|null $game_token
 * @property string|null $game_token_date
 * @property string|null $current_currency
 * @property int $bonus_use
 * @property int $bonus_daily
 * @property int $bonus_hourly
 * @property int $vk_bonus_use
 * @property int $tg_bonus_use
 * @property string|null $created_ip
 * @property string|null $used_ip
 * @property string|null $videocard
 * @property string|null $fingerprint
 * @property string|null $logs_length
 * @property int|null $current_id
 * @property float|null $current_bet
 * @property string $remember_token
 * @property int|null $auto_withdraw
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $guarded = [];

    public function actions(): User|HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function withdraws(): User|HasMany
    {
        return $this->hasMany(Withdraw::class);
    }

    public function payments(): User|HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isVip(): bool
    {
        return $this->vipInvite()->where('is_active', 1)->exists();
    }

    public function vipInvite(): HasOne|User
    {
        return $this->hasOne(VipInvite::class);
    }
}