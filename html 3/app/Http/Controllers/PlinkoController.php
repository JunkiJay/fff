<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Game;
use App\Models\Payment;
use App\Models\PlinkoData;
use App\Models\Profit;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PlinkoController extends Controller
{
    public function getMultipliers()
    {
        return PlinkoData::first()->data;
    }

    public function play(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'bet' => 'required|numeric|min:1|max:1000000',
            'pins' => 'required|integer|min:8|max:16',
            'difficulty' => [
                Rule::in(['low', 'medium', 'high']),
                'required'
            ],
        ]);

        if ($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        $bet = $request->bet;
        $pins = $request->pins;
        $difficulty = $request->difficulty;

        [$coeff, $game, $user, $win, $bucketId] = DB::transaction(function () use ($bet, $pins, $difficulty) {
            $user = User::lockForUpdate()->find($this->user->id);

            if ($user->balance < $bet) {
                return [
                    'error' => true,
                    'message' => 'Недостаточно средств'
                ];
            }

            $payments_amount = Payment::where('user_id', $this->user->id)
                ->where('status', 1)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(3))
                ->sum('sum');


            if (!$user->bonus_bank) {
                $user->bonus_bank = 100;
                $user->save();
            }

            $multipliers = $this->getMultipliers()[$difficulty][$pins];
            [$bucketId, $coeff] = $this->generateRandomNumber($multipliers);

            $profit = round(($bet * $coeff) - $bet, 2);
            $win = $profit + $bet;

            // антиминус

            $setting = Setting::query()->find(1);
            $antiminus = Profit::query()->find(1);

            if ($payments_amount > 0) {
                if ($setting->antiminus == 1 && !$this->user->is_youtuber) {
                    if ($profit > $antiminus->bank_plinko) {
                        $coeff = min($multipliers);
                        $bucketId = array_search($coeff, $multipliers);

                        $profit = round(($bet * $coeff) - $bet, 2);
                        $win = $profit + $bet;
                    }
                }
            } else {
                if ($setting->antiminus == 1 && !$this->user->is_youtuber) {
                    if ($profit > $user->bonus_bank) {
                        $coeff = min($multipliers);
                        $bucketId = array_search($coeff, $multipliers);

                        $profit = round(($bet * $coeff) - $bet, 2);
                        $win = $profit + $bet;
                    }
                }
            }

            if (!(\Cache::has('user.' . $this->user->id . '.historyBalance'))) {
                \Cache::put('user.' . $this->user->id . '.historyBalance', '[]');
            }

            $win_bool = $coeff >= 1;
            if ($win_bool) {
                $hist_balance = array(
                    'user_id' => $this->user->id,
                    'type' => 'Выигрыш в Plinko',
                    'balance_before' => round($this->user->balance, 2),
                    'balance_after' => round($this->user->balance + $profit, 2),
                    'date' => date('d.m.Y H:i:s')
                );
            } else {
                $hist_balance = array(
                    'user_id' => $this->user->id,
                    'type' => 'Ставка в Plinko',
                    'balance_before' => round($this->user->balance, 2),
                    'balance_after' => round($this->user->balance + $profit, 2),
                    'date' => date('d.m.Y H:i:s')
                );
            }


            $cashe_hist_user = \Cache::get('user.' . $this->user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $this->user->id . '.historyBalance', $cashe_hist_user);

            Action::create([
                'user_id' => $user->id,
                'action' => 'Ставка в Plinko (' . $profit . ')',
                'balanceBefore' => $user->balance,
                'balanceAfter' => $user->balance + $profit
            ]);

            $user->balance += $profit;
            $user->wager -= $bet;
            if ($user->wager < 0) {
                $user->update([
                    'wager' => 0
                ]);
            }
            $user->plinko += $profit;
            $user->save();
            if (!$this->user->is_youtuber) {
                $game = Game::create([
                    'user_id' => $user->id,
                    'game' => 'plinko',
                    'bet' => $bet,
                    'chance' => 0,
                    'win' => $win,
                    'type' => 'win',
                ]);
            }


            if ($payments_amount > 0) {
                if ($setting->antiminus == 1 && !$this->user->is_youtuber) {
                    $antiminus->bank_plinko += $profit < 1
                        ? ($profit * -1) / 100 * (100 - $antiminus->comission)
                        : -$profit;
                    $antiminus->save();
                }
            } else {
                if ($setting->antiminus == 1 && !$this->user->is_youtuber) {
                    if ($coeff >= 1) {
                        $user->decrement('bonus_bank', $profit);
                    } else {
                        if ($user->bonus_bank + $bet <= 400) {
                            $user->increment('bonus_bank', $coeff * $bet);
                        } else {
                            $user->bonus_bank = 400;
                            $user->save();
                        }
                    }
                }
            }

            return [$coeff, $game, $user, $win, $bucketId];
        });

        DB::commit();

        if ($coeff >= 1 && !$user->is_youtuber) {
            Redis::publish('newGame', json_encode([
                'id' => $game->id,
                'type' => 'plinko',
                'username' => $user->username,
                'amount' => $game->bet,
                'coeff' => $coeff,
                'result' => $win
            ]));
        }


        return [
            'bet' => $bet,
            'bucket' => $bucketId,
            'result' => $win,
            'coeff' => $coeff
        ];
    }

    private function generateRandomNumber(array $numbers)
    {
        $index = 0;
        for ($i = 0; $i < count($numbers) - 1; $i++) {
            $index += mt_rand(0, 1);
        }
        return [$index, $numbers[$index]];
    }

}