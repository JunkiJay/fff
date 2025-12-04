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

class WheelController extends Controller
{
    private $profit;
    
    public function __construct()
    {
        parent::__construct();
        $this->profit = Profit::first();
    }

    public function play(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bet' => 'required|numeric|min:1|max:1000000',
            'level' => 'required|integer|min:1|max:3',
        ]);

        if($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        $items = [];

        switch ($request->level) {
            case 1:
                // Легкий: lose 13, red 6, blue 37 (всего 56)
                for($i = 0; $i < 13; $i++) $items[] = 'lose';
                for($i = 0; $i < 6; $i++) $items[] = 'red';
                for($i = 0; $i < 37; $i++) $items[] = 'blue';
            break;

            case 2:
                // Средний: lose 26, blue 14, red 9, green 4, pink 2 (всего 55)
                for($i = 0; $i < 26; $i++) $items[] = 'lose';
                for($i = 0; $i < 14; $i++) $items[] = 'blue';
                for($i = 0; $i < 9; $i++) $items[] = 'red';
                for($i = 0; $i < 4; $i++) $items[] = 'green';
                for($i = 0; $i < 2; $i++) $items[] = 'pink';
            break;

            case 3:
                // Сложный: lose 54, pink 1 (всего 55)
                for($i = 0; $i < 54; $i++) $items[] = 'lose';
                $items[] = 'pink';
            break;
        }

        shuffle($items);

        $color = $items[array_rand($items)];
        $coef = $this->getCoef($request->level, $color);

        $totalWin = round($request->bet * $coef - $request->bet, 2);

        $payments_amount = Payment::where('user_id', $this->user->id)
        ->where('status', 1)
        ->whereDate('created_at', '>=', Carbon::now()->subDays(3))
        ->sum('sum');

        DB::beginTransaction();

        $user = User::lockForUpdate()->where('id', $this->user->id)->first();

        if(!$user->bonus_bank){
            $user->bonus_bank = 100;
            $user->save();
        }

        if($payments_amount > 0){
            if($totalWin > $this->profit->bank_wheel && !$this->user->is_youtuber) {
                $color = 'lose';
                $coef = 0;
                $totalWin = -$request->bet;
            }
        }else{
            if($totalWin > $user->bonus_bank) {
                $color = 'lose';
                $coef = 0;
                $totalWin = -$request->bet;
            }
        }

        if($user->balance < $request->bet) {
            return [
                'error' => true,
                'message' => 'Недостаточно средств на вашем балансе.'
            ];
        }

        
        Action::create([
            'user_id' => $user->id,
            'action' => 'Ставка в Wheel (' . $totalWin . ')',
            'balanceBefore' => $user->balance,
            'balanceAfter' => $user->balance + $totalWin
        ]);

        $user->balance += $totalWin;
        $user->wheel += $totalWin;
        $user->wager -= $request->bet;

        if($user->wager < 0) {
            $user->wager = 0;
        }

        $user->save();

        DB::commit();

        $totalWin += $request->bet;

        if($coef) {
            if($payments_amount > 0){
                if(!$user->is_youtuber) {
                    $this->profit->bank_wheel -= $totalWin - $request->bet;
                    $this->profit->save();
                }
            }else{
                if(!$user->is_youtuber) {
                    $user->bonus_bank -= $totalWin - $request->bet;
                    $user->save();
                }
            }
    
            Redis::publish('newGame', json_encode([
                'id' => 'wheel_' . rand(-11111111111111, 999999999999999),
                'type' => 'wheel',
                'username' => $user->username,
                'amount' => $request->bet,
                'coeff' => $coef,
                'result' => $totalWin
            ]));
            if(!$user->is_youtuber) {
                Game::create([
                    'user_id' => $user->id,
                    'game' => 'wheel',
                    'bet' => $request->bet,
                    'chance' => 100,
                    'win' => $totalWin,
                    'type' => 'win',
                ]);
            }
      
        } else {
            if($payments_amount > 0){
                if(!$user->is_youtuber) {
                    $this->profit->bank_wheel += $request->bet / 100 * (100 - $this->profit->comission);
                    $this->profit->save();
                }
            }else{
                if($user->bonus_bank + $request->bet <= 400){
                    $user->increment('bonus_bank', $request->bet);
                }else{
                    $user->bonus_bank = 400;
                    $user->save();
                }
            }
            Game::create([
                'user_id' => $user->id,
                'game' => 'wheel',
                'bet' => $request->bet,
                'chance' => 0,
                'win' => $totalWin,
                'type' => 'lose',
            ]);
        }


        return [
            'balance' => $user->balance,
            'color' => $color,
            'win' => round($totalWin, 2),
            'coef' => $coef
        ];
    }

    private function getCoef($level, $color)
    {
        $info = [
            [],
            [
                'blue' => 1.2,
                'red' => 1.5,
                'lose' => 0
            ],
            [
                'blue' => 1.2,
                'red' => 1.5,
                'green' => 3,
                'pink' => 5,
                'lose' => 0
            ],
            [
                'pink' => 49.5,
                'lose' => 0
            ]
        ];

        return $info[$level][$color];
    }
}
