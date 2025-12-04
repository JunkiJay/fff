<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Game;
use App\Models\Payment;
use App\Models\Profit;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DiceController extends Controller
{
    public function bet(Request $request) : array {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1|max:1000000',
            'chance' => 'required|numeric|min:1|max:95',
            'type' => [
                Rule::in(['min', 'max']),
                'required'
            ]
        ]);

        if($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        $bet = $request->amount;
        $chance = $request->chance;
        $type = $request->type;

        if($request->amount > $this->user->balance) {
            return [
                'error' => true,
                'message' => 'Недостаточно средств'
            ];
        }

        if($this->user->ban) {
            return [
                'error' => true,
                'message' => 'Ваш аккаунт заблокирован'
            ];
        }

        $payments_amount = Payment::where('user_id', $this->user->id)
        ->where('status', 1)
        ->whereDate('created_at', '>=', Carbon::now()->subDays(3))
        ->sum('sum');

        DB::beginTransaction();

        $this->user = User::where('id', $this->user->id)
            ->lockForUpdate()
            ->first();

        Action::create([
            'user_id' => $this->user->id,
            'action' => 'Ставка в Dice (-' . $bet . ')',
            'balanceBefore' => $this->user->balance,
            'balanceAfter' => $this->user->balance - $bet
        ]);

        $this->user->decrement('balance', $bet);
        $this->user->decrement('wager', $bet);
        $this->user->decrement('dice', $bet);

        if($this->user->wager < 0) $this->user->update([
            'wager' => 0
        ]);

        if(!$this->user->bonus_bank){
            $this->user->bonus_bank = 100;
            $this->user->save();
        }

        $random = rand(0, 999999);

        $middle['min'] = round((100 - $chance) * 10000 / 2, 0);
        $middle['max'] = round((100 - $chance) * 10000 / 2, 0) + round(($chance / 100) * 999999, 0);

        $min = round(($chance / 100) * 999999, 0);
        $max = 999999 - round(($chance / 100) * 999999, 0);


        $win = round((100 / $chance) * $bet, 2);
        $isWin = false;

        $setting = Setting::query()->find(1);
        $profit = Profit::query()->find(1);

       

        if($payments_amount > 0){
            if($setting->antiminus == 1 && !$this->user->is_youtuber) {
                if($win - $bet > $profit->bank_dice) {
                    switch($type) {
                        case 'min':
                            $random = rand(($chance * 10000) - 1, 999999);
                        break;
    
                        case 'max':
                            $random = rand(0, 1000000 - ($chance * 10000));
                        break;
                    }
                }
            }
        }else{
            if($setting->antiminus == 1 && !$this->user->is_youtuber) {
                if($win - $bet > $this->user->bonus_bank) {
                    switch($type) {
                        case 'min':
                            $random = rand(($chance * 10000) - 1, 999999);
                        break;
    
                        case 'max':
                            $random = rand(0, 1000000 - ($chance * 10000));
                        break;
                    }
                }
            }
        }

        if($payments_amount > 0){
            switch($type) {
                case 'min':
                    $random = rand(0, 999999/0.92);
                    if($random <= $min) $isWin = true;
                    if($random < 1 || $random > 999999){
                        $random = rand(($chance * 10000) - 1, 999999);
                        $isWin = false;
                    }
                break;
                case 'max':
                    $random = rand(-999999*(1/.92-1), 999999);
                    if($random >= $max) $isWin = true;
                    if($random < 1 || $random > 999999){
                        $random = rand(0, 1000000 - ($chance * 10000));
                        $isWin = false;
                    }
                break;
            }
            if($setting->antiminus == 1 && !$this->user->is_youtuber) {
                if($win - $bet > $profit->bank_dice) {
                    switch($type) {
                        case 'min':
                            $random = rand(($chance * 10000) - 1, 999999);
                            $isWin = false;
                        break;
    
                        case 'max':
                            $random = rand(0, 1000000 - ($chance * 10000));
                            $isWin = false;
                        break;
                    }
                }
            }
        }else{
            switch($type) {
                case 'min':
                    $random = rand(0, 999999/0.99);
                    if($random <= $min) $isWin = true;
                    if($random < 1 || $random > 999999){
                        $random = rand(($chance * 10000) - 1, 999999);
                        $isWin = false;
                    }
                break;
                case 'max':
                    $random = rand(-999999*(1/.99-1), 999999);
                    if($random >= $max) $isWin = true;
                    if($random < 1 || $random > 999999){
                        $random = rand(0, 1000000 - ($chance * 10000));
                        $isWin = false;
                    }
                break;
            }
            if($setting->antiminus == 1 && !$this->user->is_youtuber) {
                if($win - $bet > $this->user->bonus_bank) {
                    switch($type) {
                        case 'min':
                            $random = rand(($chance * 10000) - 1, 999999);
                            $isWin = false;
                        break;
    
                        case 'max':
                            $random = rand(0, 1000000 - ($chance * 10000));
                            $isWin = false;
                        break;
                    }
                }
            }
        }
        

        $text = 'Выпало ' . $random;

        if($isWin) {
            $win = number_format($win, 2, '.', '');
            $text = 'Выигрыш ' . $win;

            Action::create([
                'user_id' => $this->user->id,
                'action' => 'Выигрыш в Dice (+' . $win . ')',
                'balanceBefore' => $this->user->balance,
                'balanceAfter' => $this->user->balance + $win
            ]);

            $this->user->increment('balance', $win);
            $this->user->increment('dice', $win);

            if($payments_amount > 0){
                if($setting->antiminus == 1 && !$this->user->is_youtuber) {
                    $profit->update([
                        'bank_dice' => $profit->bank_dice - ($win - $bet),
                    ]);
                }
            }else{
                if($setting->antiminus == 1 && !$this->user->is_youtuber) {
                    $this->user->decrement('bonus_bank', $win - $bet);
                }
            }
        } else {
            if($payments_amount > 0){
                if(!$this->user->is_youtuber) {
                    $profit->update([
                        'bank_dice' => $profit->bank_dice + ($bet / 100) * (100 - $profit->comission),
                        'earn_dice' => $profit->earn_dice + ($bet / 100) * $profit->comission
                    ]);
                }
            }else{
                if(!$this->user->is_youtuber) {
                    if($this->user->bonus_bank + $bet <= 400){
                        $this->user->increment('bonus_bank', $bet);
                    }else{
                        $this->user->bonus_bank = 400;
                        $this->user->save();
                    }
                } 
            }
        }

        if(!$this->user->is_youtuber){
            $game = Game::create([
                'user_id' => $this->user->id,
                'game' => 'dice',
                'bet' => $bet,
                'chance' => $chance,
                'win' => $isWin ? $win : 0,
                'type' => $isWin ? 'win' : 'lose',
                'fake' => 0
            ]);
        }

        DB::commit();

        if($isWin && !$this->user->is_youtuber) {
            Redis::publish('newGame', json_encode([
                'id' => $game->id,
                'type' => 'dice',
                'username' => $this->user->username,
                'amount' => $bet,
                'coeff' => round($win / $bet, 2),
                'result' => $isWin ? $win : 0
            ]));
        }

        if(!(\Cache::has('user.'.$this->user->id.'.historyBalance'))){ \Cache::put('user.'.$this->user->id.'.historyBalance', '[]'); }

        $hist_balance =	array(
			'user_id' => $this->user->id,
			'type' => 'Ставка в Dice',
			'balance_before' => round($isWin ? $this->user->balance - ($win - $bet) : $this->user->balance + $bet, 2),
			'balance_after' => round($this->user->balance, 2),
			'date' => date('d.m.Y H:i:s')
		);

		$cashe_hist_user = \Cache::get('user.'.$this->user->id.'.historyBalance');

		$cashe_hist_user = json_decode($cashe_hist_user);
		$cashe_hist_user[] = $hist_balance;
		$cashe_hist_user = json_encode($cashe_hist_user);
		\Cache::put('user.'.$this->user->id.'.historyBalance', $cashe_hist_user);

        return [
            'status' => $isWin,
            'text' => $text,
            'balance' => $this->user->balance
        ];
    }
}
