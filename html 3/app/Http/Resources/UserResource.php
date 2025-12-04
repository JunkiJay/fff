<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'unique_id' => $this->unique_id,
            'username' => $this->username,
            'email' => $this->email,
            'balance' => $this->balance,
            'password' => $this->password,
            'reset_token' => $this->reset_token,
            'freespins' => $this->freespins,
            'fs_slot' => $this->fs_slot,
            'fs_amount' => $this->fs_amount,
            'freeround_id' => $this->freeround_id,
            'cashback' => $this->cashback,
            'promo_limit' => $this->promo_limit,
            'promo_limit2' => $this->promo_limit2,
            'bonus_bank' => $this->bonus_bank,
            'repost' => $this->repost,
            'bonus_balance' => $this->bonus_balance,
            'wager' => $this->wager,
            'wager_status' => $this->wager_status,
            'avatar' => $this->avatar,
            'vk_id' => $this->vk_id,
            'tg_id' => $this->tg_id,
            'vk_username' => $this->vk_username,
            'dice' => $this->dice,
            'mines' => $this->mines,
            'bubbles' => $this->bubbles,
            'wheel' => $this->wheel,
            'slots' => $this->slots,
            'plinko' => $this->plinko,
            'total_reposts' => $this->total_reposts,
            'is_bot' => $this->is_bot,
            'is_admin' => $this->is_admin,
            'is_youtuber' => $this->is_youtuber,
            'is_worker' => $this->is_worker,
            'referral_use' => $this->referral_use,
            'referral_send' => $this->referral_send,
            'referral_balance' => $this->referral_balance,
            'ref_1_lvl' => $this->ref_1_lvl,
            'ref_2_lvl' => $this->ref_2_lvl,
            'ref_3_lvl' => $this->ref_3_lvl,
            'ban' => $this->ban,
            'bonus_use' => $this->bonus_use,
            'bonus_daily' => $this->bonus_daily,
            'bonus_hourly' => $this->bonus_hourly,
            'vk_bonus_use' => $this->vk_bonus_use,
            'tg_bonus_use' => $this->tg_bonus_use,
            'created_ip' => $this->created_ip,
            'used_ip' => $this->used_ip,
            'videocard' => $this->videocard,
            'fingerprint' => $this->fingerprint,
            'logs_length' => $this->logs_length,
            'current_id' => $this->current_id,
            'current_bet' => $this->current_bet,
            'remember_token' => $this->remember_token            
        ];
    }
}
