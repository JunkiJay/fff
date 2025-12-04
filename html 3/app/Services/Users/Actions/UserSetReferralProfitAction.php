<?php

declare(strict_types=1);

namespace App\Services\Users\Actions;

use App\Models\ReferralProfit;
use App\Models\User;
use FKS\Actions\Action;
use Illuminate\Support\Facades\DB;

/**
 * @method static void run(User $user, float $amount)
 */
class UserSetReferralProfitAction extends Action
{
    public function handle(User $user, float $amount): void
    {
        if (is_null($user->referral_use)) {
            return;
        }

        $amount = $amount / 100;

        DB::beginTransaction();

        $referralFirstLvl = User::find($user->referral_use);
        $referralSecondLvl = $referralFirstLvl ? User::find($referralFirstLvl->referral_use) : null;
        $referralThirdLvl = $referralSecondLvl ? User::find($referralSecondLvl->referral_use) : null;

        if (!is_null($referralFirstLvl)) {
            $percent = 10;

            if ($referralFirstLvl->ref_1_lvl > 0) {
                $percent = $referralFirstLvl->ref_1_lvl;
            }

            $referralFirstLvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referralFirstLvl->id,
                'amount' => $amount * $percent,
                'level' => 1
            ]);
        }

        if (!is_null($referralSecondLvl)) {
            $percent = 3;

            if ($referralSecondLvl->ref_2_lvl > 0) {
                $percent = $referralSecondLvl->ref_2_lvl;
            }

            $referralSecondLvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referralSecondLvl->id,
                'amount' => $amount * $percent,
                'level' => 2
            ]);
        }

        if (!is_null($referralThirdLvl)) {
            $percent = 2;

            if ($referralThirdLvl->ref_3_lvl > 0) {
                $percent = $referralThirdLvl->ref_3_lvl;
            }

            $referralThirdLvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referralThirdLvl->id,
                'amount' => $amount * $percent,
                'level' => 3
            ]);
        }

        DB::commit();
    }
}