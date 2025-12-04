<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Game;
use App\Models\Mine;
use App\Models\Payment;
use App\Models\Profit;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class MinesController extends Controller
{
    protected $profit;

    public function __construct()
    {
        parent::__construct();
        $this->profit = Profit::first();
    }

    protected $coef = [
        2 => [
            1.04,
            1.12,
            1.23,
            1.35,
            1.5,
            1.66,
            1.86,
            2.1,
            2.35,
            2.7,
            3.12,
            3.65,
            4.3,
            5.2,
            6.3,
            7.9,
            10,
            13.4,
            19,
            28.5,
            47,
            95,
            285
        ],
        3 => [
            1.07,
            1.23,
            1.4,
            1.65,
            1.9,
            2.23,
            2.67,
            3.2,
            3.9,
            4.8,
            6,
            7.6,
            9.9,
            13.3,
            18.3,
            26,
            39,
            62,
            110,
            220,
            550,
            2200
        ],
        4 => [
            1.12,
            1.35,
            1.65,
            2,
            2.45,
            3.1,
            3.9,
            5,
            6.55,
            8.8,
            12,
            16.6,
            24.5,
            36.6,
            57,
            95,
            170,
            340,
            800,
            2400,
            12000
        ],
        5 => [1.18, 1.5, 1.9, 2.5, 3.25, 4.35, 5.9, 8.2, 11.5, 16.8, 25, 39, 64, 110, 200, 400, 900, 2400, 8400, 50000],
        6 => [1.25, 1.66, 2.25, 3.1, 4.3, 6.2, 9.1, 13.5, 21, 33.5, 56, 98, 182, 365, 800, 2000],
        7 => [1.3, 1.85, 2.7, 3.9, 5.9, 9, 14.5, 23.5, 40, 71, 133, 265, 575, 1380, 3800, 12700],
        8 => [1.4, 2.1, 3.2, 5, 8.2, 13.5, 23.5, 42, 80, 160, 340, 800, 2050, 6200, 23000, 115000, 1005000],
        9 => [1.5, 2.4, 3.9, 6.6, 11.5, 21, 40, 80, 170, 390, 970, 2700, 8800, 35500, 195000, 1900000],
        10 => [1.57, 2.7, 4.8, 8.8, 16.7, 33.3, 71, 160, 390, 1030, 3100, 10800, 47500, 285000, 3100000],
        11 => [1.7, 3.1, 6, 12, 25, 56, 133, 340, 960, 3100, 11500, 54000, 350000, 4200000],
        12 => [1.82, 3.65, 7.6, 16.5, 39, 98, 265, 800, 2700, 10750, 54500, 370000, 4900000],
        13 => [2, 4.3, 10, 24.5, 64, 180, 580, 2050, 8900, 47500, 355000, 4950000],
        14 => [2.15, 5.2, 13, 36, 110, 365, 1390, 6250, 35500, 285000, 4250000],
        15 => [2.35, 6.3, 18, 57, 200, 805, 3800, 22800, 195000, 3100000],
        16 => [2.65, 8, 26, 95, 405, 2000, 12700, 115000, 1950000],
        17 => [3, 10, 39, 170, 910, 6000, 57500, 1000000],
        18 => [3.4, 13.5, 63, 350, 2400, 24000, 460000],
        19 => [4, 19, 107, 805, 8500, 170000],
        20 => [4.8, 28.5, 220, 2400, 50000],
        21 => [6, 47, 550, 12000],
        22 => [8, 95, 2200],
        23 => [12, 285],
        24 => [24]
    ];

    public function init()
    {
        $game = Mine::where('user_id', $this->user->id)->where('status', 0)->first();

        if (!$game) {
            return 0;
        }

        $grid = json_decode($game->grid, true);
        $totalWin = !$game->step
            ? 0
            : $game->amount * $this->coef[$game->bombs][$game->step - 1];

        return [
            'bombs' => $game->bombs,
            'amount' => $game->amount,
            'click' => $grid['click'],
            'total' => $totalWin
        ];
    }

    public function createGame(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
            'bombs' => 'required|integer|min:2|max:25',
        ]);

        if ($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        if ($this->user->ban) {
            return [
                'error' => true,
                'message' => 'Ваш аккаунт заблокирован'
            ];
        }

        try {
            DB::beginTransaction();

            $user = User::where('id', $this->user->id)->lockForUpdate()->first();
            $game = Mine::where('user_id', $this->user->id)->where('status', 0)->first();

            if ($game) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'У вас есть активная игра'
                ];
            }

            if ($request->amount > $user->balance) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'Недостаточно средств'
                ];
            }

            Action::create([
                'user_id' => $user->id,
                'action' => 'Ставка в Mines (-' . $request->amount . ')',
                'balanceBefore' => $user->balance,
                'balanceAfter' => $user->balance - $request->amount
            ]);

            $user->decrement('balance', $request->amount);
            $user->decrement('wager', $request->amount);

            if ($user->wager < 0) {
                $user->update([
                    'wager' => 0
                ]);
            }

            Mine::create([
                'user_id' => $user->id,
                'amount' => $request->amount,
                'bombs' => $request->bombs,
                'grid' => $this->generateGrid($request),
                'status' => 0
            ]);


            if (Mine::where('status', 0)->where('user_id', $user->id)->count() >= 2) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'У вас есть активная игра'
                ];
            }

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $this->user->id,
                'type' => 'Ставка в Mines',
                'balance_before' => round($this->user->balance, 2),
                'balance_after' => round($this->user->balance - $request->amount, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $this->user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $this->user->id . '.historyBalance', $cashe_hist_user);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'error' => true,
                'message' => 'Подождите...'
            ];
        }

        return [
            'message' => 'Игра создана',
            'balance' => $user->balance
        ];
    }

    public function openPath(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'path' => 'required|integer|min:1|max:25',
        ]);

        if ($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }

        try {
            DB::beginTransaction();

            $game = Mine::where('user_id', $this->user->id)
                ->where('status', 0)
                ->sharedLock()
                ->first();

            if (!$game) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'У вас нет активной игры'
                ];
            }

            $isWin = false;
            $totalWin = 0;

            $grid = json_decode($game->grid, true);

            if (in_array($request->path, $grid['click'])) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'Вы уже нажимали на это поле'
                ];
            }
            $payments_amount = Payment::where('user_id', $this->user->id)
                ->where('status', 1)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(3))
                ->sum('sum');
            $totalWin = $game->amount * $this->coef[$game->bombs][$game->step];

            if (!$this->user->bonus_bank) {
                $this->user->bonus_bank = 100;
                $this->user->save();
            }

            if ($payments_amount > 0) {
                if (
                    $this->config->antiminus &&
                    $game->amount * $this->coef[$game->bombs][$game->step] - $game->amount > $this->profit->bank_mines &&
                    !in_array($request->path, $grid['bombs']) &&
                    !$this->user->is_youtuber
                ) {
                    array_splice($grid['bombs'], -1, 1, $request->path);

                    $game->grid = $grid;
                    $game->save();
                }
            } else {
                if (!$this->user->bonus_bank) {
                    $this->user->bonus_bank = 100;
                }
                if (
                    $this->config->antiminus
                    && $game->amount * $this->coef[$game->bombs][$game->step] - $game->amount > $this->user->bonus_bank
                    && !in_array($request->path, $grid['bombs'])
                    && !$this->user->is_youtuber
                ) {
                    array_splice($grid['bombs'], -1, 1, $request->path);

                    $game->grid = $grid;
                    $game->save();
                }
            }

            // Проверяем, что это первый клик
            $isFirstClick = $game->step == 0 || empty($grid['click']);

            // Шанс проигрыша 95% при первом клике
            if ($isFirstClick) {
                $shouldLose = mt_rand(1, 100) > 95;

                if ($shouldLose && !in_array($request->path, $grid['bombs'])) {
                    // Насильно превращаем выбранную клетку в бомбу
                    array_splice($grid['bombs'], -1, 1, [$request->path]);
                    $game->grid = $grid;
                    $game->save();
                }
            }

            if (!in_array($request->path, $grid['bombs'])) {
                $isWin = true;
                $game->increment('step', 1);

                $grid['click'][] = $request->path;

                $game->grid = $grid;
                $game->save();
            }

            if (!$isWin) {
                $totalWin = $game->amount * $this->coef[$game->bombs][!$game->step ? $game->step : $game->step - 1];

                $game->update(['status' => 1]);
                $this->user->decrement('mines', $game->amount);

                Action::create([
                    'user_id' => $this->user->id,
                    'action' => 'Проигрыш в Mines (-' . $game->amount . ')',
                    'balanceBefore' => $this->user->balance + $game->amount,
                    'balanceAfter' => $this->user->balance
                ]);

                if (!$this->user->is_youtuber) {
                    Game::create([
                        'user_id' => $this->user->id,
                        'game' => 'mines',
                        'bet' => $game->amount,
                        'chance' => 100,
                        'win' => 0,
                        'type' => 'lose',
                        'fake' => 0
                    ]);
                    if ($payments_amount > 0) {
                        $this->profit->increment(
                            'bank_mines',
                            $game->amount * ((100 - $this->profit->comission) / 100)
                        );
                        $this->profit->increment('earn_mines', $game->amount * ($this->profit->comission) / 100);
                    } else {
                        if ($this->user->bonus_bank + $game->amount <= 400) {
                            $this->user->increment('bonus_bank', $game->amount);
                        } else {
                            $this->user->bonus_bank = 400;
                            $this->user->save();
                        }
                    }
                }
            }

            $instWin = null;

            if ($isWin && !isset($this->coef[$game->bombs][$game->step])) {
                $instWin = $this->take();
            }

            DB::commit();
        } catch (\Exception $e) {
            \Log::debug($e);

            DB::rollback();

            return [
                'error' => true,
                'message' => 'Подождите...'
            ];
        }

        return [
            'total' => $totalWin,
            'continue' => $isWin,
            'bombs' => $isWin ? [] : $grid['bombs'],
            'step' => $game->step,
            'instwin' => $instWin,
            'balance' => $this->user->balance
        ];
    }

    public function take()
    {
        DB::beginTransaction();

        $user = User::where('id', $this->user->id)->lockForUpdate()->first();
        $game = Mine::where('status', 0)->where('user_id', $this->user->id)->lockForUpdate()->first();

        if (!$game) {
            DB::rollback();
            return [
                'error' => true,
                'message' => 'У вас нет активной игры'
            ];
        }

        if ($game->step == 0) {
            DB::rollback();
            return [
                'error' => true,
                'message' => 'Сделайте 1 ход'
            ];
        }

        $game->status = 1;
        $game->save();

        $payments_amount = Payment::where('user_id', $this->user->id)
            ->where('status', 1)
            ->whereDate('created_at', '>=', Carbon::now()->subDays(3))
            ->sum('sum');

        $totalWin = $game->amount * $this->coef[$game->bombs][$game->step - 1];


        Action::create([
            'user_id' => $user->id,
            'action' => 'Выигрыш в Mines (+' . $totalWin . ')',
            'balanceBefore' => $user->balance,
            'balanceAfter' => $user->balance + $totalWin
        ]);

        $user->increment('balance', $totalWin);
        $user->increment('mines', $totalWin - $game->amount);

        DB::commit();

        $grid = json_decode($game->grid, true);
        if (!$user->is_youtuber) {
            Redis::publish('newGame', json_encode([
                'id' => $game->id,
                'type' => 'mines',
                'username' => $user->username,
                'amount' => $game->amount,
                'coeff' => round($totalWin / $game->amount, 2),
                'result' => $totalWin
            ]));
        }

        if (!$user->is_youtuber) {
            Game::create([
                'user_id' => $user->id,
                'game' => 'mines',
                'bet' => $game->amount,
                'chance' => 100,
                'win' => $totalWin,
                'type' => 'win',
                'fake' => 0
            ]);
            if ($payments_amount > 0) {
                $this->profit->decrement('bank_mines', $totalWin - $game->amount);
            } else {
                $this->user->decrement('bonus_bank', $totalWin - $game->amount);
            }
        }

        return [
            'total' => $totalWin,
            'coeff' => round($totalWin / $game->amount, 2),
            'bombs' => $grid['bombs'],
            'balance' => $user->balance
        ];
    }

    public function generateGrid($data)
    {
        $bombs = range(1, 25);
        shuffle($bombs);
        $bombs = array_slice($bombs, 0, $data->bombs);

        $grid = [
            'bombs' => $bombs,
            'click' => []
        ];

        return json_encode($grid, true);
    }
}
