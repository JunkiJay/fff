<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\BonuseLog;
use App\Models\Payment;
use App\Models\ReferralProfit;
use App\Models\User;
use Cache;
use DB;
use Illuminate\Http\Request;

class ReferralController extends Controller
{
    public function init()
    {
        $referral_lvl_1_list = User::where('referral_use', $this->user->id)
            ->pluck('id');
        $referral_lvl_2_list = User::whereIn('referral_use', $referral_lvl_1_list)
            ->pluck('id');
        $referral_lvl_3_list = User::whereIn('referral_use', $referral_lvl_2_list)
            ->pluck('id');
        // milestone-бонусы и депозиты считаются только по 1 уровню

        // milestone-бонусы
        $milestones = [
            ['reward' => 500, 'deposit' => 5000],
            ['reward' => 1000, 'deposit' => 15000],
            ['reward' => 1500, 'deposit' => 50000],
        ];

        // milestone-бонусы и депозиты только по 1 уровню
        $all_ref_ids = $referral_lvl_1_list->unique()->values();

        // Сумма депозитов всех рефералов всех уровней
        $totalDeposits = Payment::whereIn('user_id', $all_ref_ids)
            ->where('status', 1)
            ->sum('sum');

        $totalDeposits_milestone = Payment::whereIn('user_id', $all_ref_ids)
            ->where('status', 1)
            ->where('created_at', '>=', '2025-06-27 00:00:00')
            ->sum('sum');

        $bonus_rewards = [];
        foreach ($milestones as $milestone) {
            $alreadyGiven = BonuseLog::where('user_id', $this->user->id)
                ->where('type', 'ref_milestone')
                ->where('meta', $milestone['deposit'])
                ->sum('size');
            $available = max(0, $milestone['reward'] - $alreadyGiven);
            $active = $totalDeposits_milestone >= $milestone['deposit'] && $available > 0;
            $collectedFlag = $available == 0 && $alreadyGiven > 0;
            $bonus_rewards[] = [
                'reward' => $milestone['reward'],
                'deposit' => $milestone['deposit'],
                'active' => $active,
                'collected' => $collectedFlag,
                'already_given' => $alreadyGiven,
                'available' => $available,
            ];
        }

        return [
            'data' => [
                'lvl_1' => [
                    'count' => count($referral_lvl_1_list),
                    'income' => round(
                        ReferralProfit::whereIn('from_id', $referral_lvl_1_list)->where('level', 1)->sum('amount'),
                        2
                    )
                ],
                'lvl_2' => [
                    'count' => count($referral_lvl_2_list),
                    'income' => round(
                        ReferralProfit::whereIn('from_id', $referral_lvl_2_list)->where('level', 2)->sum('amount'),
                        2
                    )
                ],
                'lvl_3' => [
                    'count' => count($referral_lvl_3_list),
                    'income' => round(
                        ReferralProfit::whereIn('from_id', $referral_lvl_3_list)->where('level', 3)->sum('amount'),
                        2
                    )
                ],
            ],
            'ref_income' => $this->user->referral_balance,
            'ref_reward' => $this->config->referral_reward,
            'link' => $this->config->referral_domain . '/r/' . $this->user->unique_id,
            'bonus_rewards' => $bonus_rewards,
            'total_ref_deposits' => $totalDeposits,
        ];
    }

    public function takeMilestoneBonus(Request $request)
    {
        $user = $request->user();
        $milestones = [
            ['deposit' => 5000, 'reward' => 500],
            ['deposit' => 15000, 'reward' => 1000],
            ['deposit' => 50000, 'reward' => 1500],
        ];

        // Собираем всех рефералов 1, 2, 3 уровня в один массив
        $referral_lvl_1_list = User::where('referral_use', $user->id)->pluck('id');
        // milestone-бонусы и депозиты только по 1 уровню
        $all_ref_ids = $referral_lvl_1_list->unique()->values();

        // Сумма депозитов всех рефералов всех уровней
        $totalDeposits = Payment::whereIn('user_id', $all_ref_ids)
            ->where('status', 1)
            ->where('created_at', '>=', '2025-06-27 00:00:00')
            ->sum('sum');

        // Уже собранные milestone-бонусы
        $bonusesGiven = [];
        foreach ($milestones as $milestone) {
            $alreadyGiven = BonuseLog::where('user_id', $user->id)
                ->where('type', 'ref_milestone')
                ->where('meta', $milestone['deposit'])
                ->sum('size');
            $available = max(0, $milestone['reward'] - $alreadyGiven);
            $active = $totalDeposits >= $milestone['deposit'] && $available > 0;
            if ($active && $available > 0) {
                $user->increment('balance', $available);
                $user->increment('wager', $available * 3);
                BonuseLog::create([
                    'user_id' => $user->id,
                    'type' => 'ref_milestone',
                    'meta' => $milestone['deposit'],
                    'size' => $available,
                ]);
                $bonusesGiven[] = [
                    'deposit' => $milestone['deposit'],
                    'reward' => $milestone['reward'],
                    'given' => $available,
                ];
            }
        }
        if (empty($bonusesGiven)) {
            return response()->json(['success' => false, 'message' => 'Нет доступных бонусов для сбора'], 200);
        }
        return response()->json([
            'success' => true,
            'bonuses' => $bonusesGiven,
            'balance' => $user->balance,
            'message' => 'Бонусы успешно зачислены',
        ]);
    }

    public function take(Request $request)
    {
        $user = $request->user();
        DB::beginTransaction();

        $user = User::where('id', $user->id)->lockForUpdate()->first();

        if ($user->referral_balance < 20) {
            return [
                'error' => true,
                'message' => 'Минимальный вывод 20 ₽'
            ];
        }

        $refBal = $user->referral_balance;

        Action::create([
            'user_id' => $user->id,
            'action' => 'referal (+' . $user->referral_balance . ')',
            'balanceBefore' => $user->balance,
            'balanceAfter' => $user->balance + $user->referral_balance
        ]);

        $user->balance += $user->referral_balance;
        $user->wager += $user->referral_balance * 3;
        $user->referral_balance = 0;
        $user->save();

        if (!(Cache::has('user.' . $this->user->id . '.historyBalance'))) {
            Cache::put('user.' . $this->user->id . '.historyBalance', '[]');
        }

        $hist_balance = array(
            'user_id' => $this->user->id,
            'type' => 'Сбор рефки',
            'balance_before' => round($user->balance - $refBal, 2),
            'balance_after' => round($user->balance, 2),
            'date' => date('d.m.Y H:i:s')
        );

        BonuseLog::create([
            'user_id' => $user->id,
            'type' => 'ref',
            'size' => round($refBal, 2)
        ]);

        $cashe_hist_user = Cache::get('user.' . $this->user->id . '.historyBalance');

        $cashe_hist_user = json_decode($cashe_hist_user);
        $cashe_hist_user[] = $hist_balance;
        $cashe_hist_user = json_encode($cashe_hist_user);
        Cache::put('user.' . $this->user->id . '.historyBalance', $cashe_hist_user);

        DB::commit();

        return [
            'balance' => $user->balance
        ];
    }

    public function setReferral($unique_id)
    {
        Session(['ref' => $unique_id]);
        return redirect('/');
    }
}
