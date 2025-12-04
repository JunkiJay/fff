<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $kassa_id
 * @property string|null $kassa_secret1
 * @property string|null $kassa_secret2
 * @property string|null $kassa_key
 * @property string|null $wallet_id
 * @property string|null $wallet_secret
 * @property string|null $wallet_desc
 * @property string|null $vlito_id
 * @property string|null $vlito_secret
 * @property float|null $min_payment_sum
 * @property float|null $min_bonus_sum
 * @property float|null $min_withdraw_sum
 * @property int|null $min_dep_withdraw
 * @property int|null $withdraw_request_limit
 * @property string|null $vk_url
 * @property string|null $tg_channel
 * @property string|null $tg_bot
 * @property string|null $vk_id
 * @property string|null $vk_token
 * @property string|null $vk_service_token
 * @property float|null $bot_timer
 * @property int $file_version
 * @property int $antiminus
 * @property float $daily_bonus_min
 * @property float $daily_bonus_max
 * @property float $hourly_bonus_min
 * @property float $hourly_bonus_max
 * @property float $onetime_bonus
 * @property int|null $telegram_chat_id
 * @property string|null $telegram_token
 * @property string|null $referral_domain
 * @property float $referral_reward
 * @property int $deposit_per_n
 * @property float $deposit_sum_n
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Setting extends Model
{
    protected $guarded = [];
}
