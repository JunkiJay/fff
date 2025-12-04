<?php

declare(strict_types=1);

namespace App\Services\Promocodes;

use App\Models\Promocode;
use App\Models\PromocodeActivation;
use App\Models\User;
use App\Services\Promocodes\DTO\PromocodeBonusDTO;
use DomainException;

class PromocodeService
{
    public function applyCode(string $code, User $user): PromocodeBonusDTO
    {
        $promo = Promocode::where('name', $code)->lockForUpdate()->first();

        $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

        if ($allUsed >= $promo->activation) {
            throw new DomainException('Промокод закончился');
        }

        $used = PromocodeActivation::where(
            [
                'promo_id' => $promo->id,
                'user_id' => $user->id
            ]
        )->exists();

        if ($used) {
            throw new DomainException('Вы уже использовали этот код');
        }

        if (strtotime($promo->end_time) <= time()) {
            throw new DomainException('Время промокода закончилось');
        }

        PromocodeActivation::create([
            'promo_id' => $promo->id,
            'user_id' => $user->id
        ]);

        return new PromocodeBonusDTO($promo->sum, $promo->wager);
    }
}