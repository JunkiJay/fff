<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Game;
use App\Models\Payment;
use App\Models\Profit;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class BubblesController extends Controller
{
    protected $profit;

    public function __construct()
    {
        parent::__construct();
        $this->profit = Profit::first();
    }

    public function play(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bet' => 'required|numeric|min:1|max:9999999',
            'goal' => 'required|numeric|min:1.05|max:1000000',
        ]);

        if($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        $bet = $request->bet;
        $goal = $request->goal;

        $random = rand(0, 999999);
        $chance = 100 / $goal;

        $win = ($bet * $goal) - $bet;
        $isWin = false;
        $coef = round(920000 / ($random + 1), 2);

        if($coef >= $goal) $isWin = true;

        $payments_amount = Payment::where('user_id', $this->user->id)
            ->where('status', 1)
            ->whereDate('created_at', '>=', Carbon::now()->subDays(3))
            ->sum('sum');

        try {
            DB::beginTransaction();

            $user = User::where('id', $this->user->id)->lockForUpdate()->first();

            if($user->balance < $bet) {
                return [
                    'error' => true,
                    'message' => 'Недостаточно средств'
                ];
            }
            if(!$user->bonus_bank){
                $user->bonus_bank = 100;
                $user->save();
            }

            if($payments_amount > 0){
                if($this->config->antiminus == 1 && !$user->is_youtuber) {
                    if($win > $this->profit->bank_bubbles) {
                        $coef = round(rand(100, $goal * 100 - 1) / 100, 2);
                        $isWin = false;
                    }
                }
                if ($isWin && mt_rand(1, 100) <= 3) {
                    $coef = round(rand(100, $goal * 100 - 1) / 100, 2);
                    $isWin = false;
                }
            }else{
                
                if($this->config->antiminus == 1 && !$user->is_youtuber) {
                    if($win > $user->bonus_bank) {
                        $coef = round(rand(100, $goal * 100 - 1) / 100, 2);
                        $isWin = false;
                    }
                }
                if ($isWin && mt_rand(1, 100) <= 1) {
                    $coef = round(rand(100, $goal * 100 - 1) / 100, 2);
                    $isWin = false;
                }
            }

            $user->decrement('wager', $bet);
            if($user->wager < 0) $user->update([
                'wager' => 0
            ]);    

            if($isWin) {
                Action::create([
                    'user_id' => $user->id,
                    'action' => 'Ставка в Bubbles (+' . $win . ')',
                    'balanceBefore' => $user->balance,
                    'balanceAfter' => $user->balance + $win
                ]);
                $user->increment('balance', $win);
                $user->increment('bubbles', $win);
                if($payments_amount > 0){
                    if($this->config->antiminus == 1 && !$user->is_youtuber) {
                        $this->profit->update([
                            'bank_bubbles' => $this->profit->bank_bubbles - $win,
                        ]);
                    }
                }else{
                    if($this->config->antiminus == 1 && !$user->is_youtuber) {
                        $user->decrement('bonus_bank', $win);
                    }
                }
       
                if(!$user->is_youtuber) {
                    Game::create([
                        'user_id' => $user->id,
                        'game' => 'bubbles',
                        'bet' => $bet,
                        'chance' => 100,
                        'win' => $isWin ? ($win + $bet) : 0,
                        'type' => 'win',
                        'fake' => 0
                    ]);
                }

                $text = 'Выигрыш ' . number_format($win + $bet, 2, '.', '');
            } else {

                Action::create([
                    'user_id' => $user->id,
                    'action' => 'Ставка в Bubbles (-' . $bet . ')',
                    'balanceBefore' => $user->balance,
                    'balanceAfter' => $user->balance - $bet
                ]);
                $user->decrement('balance', $bet);
                $user->decrement('bubbles', $bet);

                if(!$user->is_youtuber) {
                    Game::create([
                        'user_id' => $user->id,
                        'game' => 'bubbles',
                        'bet' => $bet,
                        'chance' => 0,
                        'win' =>  0,
                        'type' => 'lose',
                        'fake' => 0
                    ]);
                }

                if($payments_amount > 0){
                    if(!$user->is_youtuber) {
                        $this->profit->update([
                            'bank_bubbles' => $this->profit->bank_bubbles + ($bet / 100) * (100 - $this->profit->comission),
                            'earn_bubbles' => $this->profit->earn_bubbles + ($bet / 100) * $this->profit->comission
                        ]);
                    }
                }else{
                    if(!$user->is_youtuber) {
                        if($user->bonus_bank + $bet <= 400){
                            $user->increment('bonus_bank', $bet);
                        }else{
                            $user->bonus_bank = 400;
                            $user->save();
                        }
                    } 
                }

                $text = 'Выпало ' . number_format($coef, 2, '.', '');
            }

            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        if($isWin && !$user->is_youtuber) {
            Redis::publish('newGame', json_encode([
                'id' => rand(10000, 99999999999),
                'type' => 'bubbles',
                'username' => $user->username,
                'amount' => $bet,
                'coeff' => round(($win + $bet) / $bet, 2),
                'result' => $isWin ? ($win + $bet) : 0
            ]));
    
        }

        if(!(\Cache::has('user.'.$this->user->id.'.historyBalance'))){ \Cache::put('user.'.$this->user->id.'.historyBalance', '[]'); }

        $hist_balance =	array(
			'user_id' => $this->user->id,
			'type' => 'Ставка в Bubbles',
			'balance_before' => round($this->user->balance, 2),
			'balance_after' => round($isWin ? $this->user->balance + $win : $this->user->balance - $bet, 2),
			'date' => date('d.m.Y H:i:s')
		);

		$cashe_hist_user = \Cache::get('user.'.$this->user->id.'.historyBalance');

		$cashe_hist_user = json_decode($cashe_hist_user);
		$cashe_hist_user[] = $hist_balance;
		$cashe_hist_user = json_encode($cashe_hist_user);
		\Cache::put('user.'.$this->user->id.'.historyBalance', $cashe_hist_user);

        return [
            'text' => $text,
            'isWin' => $isWin,
            'balance' => $user->balance,
            'success' => true
        ];
    }
}