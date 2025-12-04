<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\BonuseLog;
use App\Models\Payment;
use App\Models\Promocode;
use App\Models\PromocodeActivation;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class PromoController extends Controller
{
    public function activate(Request $request)
    {
        $userId = $request->user()->id;
        $lockKey = "{$userId}:activatePromo";
        $lockAcquired = Redis::setnx($lockKey, 'locked');

        // Если блокировка успешно установлена (то есть этот запрос владеет блокировкой)
        if ($lockAcquired) {
            // Устанавливаем время жизни блокировки
            Redis::expire($lockKey, 1); // Время жизни блокировки в секундах

            try {
                // Выполните желаемую операцию
                return $this->activate_promo($request);
            } catch (\Exception $e) {
                // Обрабатываем исключение и освобождаем блокировку
                Redis::del($lockKey);
                return [
                    'error' => true,
                    'message' => 'Слишком много запросов. Пожалуйста, подождите.'
                ];
            }

            // Освобождаем блокировку после завершения операции
            Redis::del($lockKey);
        } else {
            // Если блокировка уже установлена
            return [
                'error' => true,
                'message' => 'Слишком много запросов. Пожалуйста, подождите.'
            ];
        }
    }

    private function activate_promo(Request $request)
    {
        try {
            $code = $request->code;
            $promo = Promocode::where('name', $code)->first();
            $user = $request->user();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->only_private_club === 1 && !$user->isVip()) {
                return [
                    'error' => true,
                    'message' => 'Промокод только для участников VIP-Клуба'
                ];
            }

            if ($promo->type === 'freespins') {

                $startDate = Carbon::now()->subDays($promo->deposits_days)->startOfDay();
                $endDate = Carbon::now()->endOfDay();

                $sumPayments = Payment::where('user_id', $user->id)
                    ->where('status', 1)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('sum');

                $sum = $promo->min_deposits - $sumPayments;

                if ($promo->min_deposits > $sumPayments) {
                    return [
                        'error' => true,
                        'message' => 'Для активации промокода необходимо пополнить на сумму: ' . $sum . ' ₽'
                    ];
                }

                $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

                if ($allUsed >= $promo->activation) {
                    return [
                        'error' => true,
                        'message' => 'Промокод закончился'
                    ];
                }

                $used = PromocodeActivation::where('promo_id', $promo->id)
                    ->where('user_id', $user->id)
                    ->first();

                if ($used) {
                    return [
                        'error' => true,
                        'message' => 'Вы уже использовали этот промокод!'
                    ];
                }

                if (strtotime($promo->end_time) <= time()) {
                    return [
                        'error' => true,
                        'message' => 'Время промокода закончилось'
                    ];
                }

                
                if ($this->user->fs_promo_limit && $this->user->fs_promo_limit2 && strtotime(Carbon::now()) - strtotime($this->user->fs_promo_limit) < 86400 && strtotime(Carbon::now()) - strtotime($this->user->fs_promo_limit2) < 86400) {
                    return [
                        'error' => true,
                        'message' => 'Вы исчерпали свой ежедневный лимит'
                    ];
                }
    
                if (!$this->user->fs_promo_limit) {
                    $this->user->fs_promo_limit = Carbon::now();
                    $this->user->save();
                } elseif (strtotime(Carbon::now()) - strtotime($this->user->fs_promo_limit) < 86400 && !$this->user->fs_promo_limit2) {
                    $this->user->fs_promo_limit2 = Carbon::now();
                    $this->user->save();
                } elseif (strtotime(Carbon::now()) - strtotime($this->user->fs_promo_limit) > 86400) {
                    $this->user->fs_promo_limit = Carbon::now();
                    $this->user->save();
                } elseif (strtotime(Carbon::now()) - strtotime($this->user->fs_promo_limit) < 86400 && strtotime(Carbon::now()) - strtotime($this->user->fs_promo_limit2) > 86400) {
                    $this->user->fs_promo_limit2 = Carbon::now();
                    $this->user->save();
                }

                $res = PromocodeActivation::create([
                    'promo_id' => $promo->id,
                    'user_id' => $user->id
                ]);


                if ($res) {
                    return [
                        'success' => true,
                        'message' => 'Промокод успешно активирован!',
                        'type'    => 'fs',
                        'slot_id' => $promo->id_spin
                    ];
                }
            }


            DB::beginTransaction();

            if ($promo->type != 'balance') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Пополнить"'
                ];
            }

            $startDate = Carbon::now()->subDays($promo->deposits_days)->startOfDay();
            $endDate = Carbon::now()->endOfDay();

            $sumPayments = Payment::where('user_id', $user->id)
                ->where('status', 1)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('sum');

            $sum = $promo->min_deposits - $sumPayments;

            if ($promo->min_deposits > $sumPayments) {
                return [
                    'error' => true,
                    'message' => 'Для активации промокода необходимо пополнить на сумму: ' . $sum . ' ₽'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where('promo_id', $promo->id)
                ->where('user_id', $user->id)
                ->first();

            if ($used) {
                return [
                    'error' => true,
                    'message' => 'Вы уже использовали этот код'
                ];
            }

            if (strtotime($promo->end_time) <= time()) {
                return [
                    'error' => true,
                    'message' => 'Время промокода закончилось'
                ];
            }
            $twink = User::where('created_ip', $this->user->created_ip)->where('id', '!=', $this->user->id)->first();
            $psum = Payment::where([['created_at', '>=', Carbon::today()->subDays(7)], ['user_id', $this->user->id], ['status', 1]])->sum('sum');
            if ($twink && $psum < 200) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'Чтобы разблокировать ввод промокодов, пополните баланс на 200 рублей, за последние 7 дней.'
                ];
            }

            if ($this->user->promo_limit && $this->user->promo_limit2 && strtotime(Carbon::now()) - strtotime($this->user->promo_limit) < 86400 && strtotime(Carbon::now()) - strtotime($this->user->promo_limit2) < 86400) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'Вы исчерпали свой ежедневный лимит'
                ];
            }

            if (!$this->user->promo_limit) {
                $this->user->promo_limit = Carbon::now();
                $this->user->save();
            } elseif (strtotime(Carbon::now()) - strtotime($this->user->promo_limit) < 86400 && !$this->user->promo_limit2) {
                $this->user->promo_limit2 = Carbon::now();
                $this->user->save();
            } elseif (strtotime(Carbon::now()) - strtotime($this->user->promo_limit) > 86400) {
                $this->user->promo_limit = Carbon::now();
                $this->user->save();
            } elseif (strtotime(Carbon::now()) - strtotime($this->user->promo_limit) < 86400 && strtotime(Carbon::now()) - strtotime($this->user->promo_limit2) > 86400) {
                $this->user->promo_limit2 = Carbon::now();
                $this->user->save();
            }

            Action::create([
                'user_id' => $this->user->id,
                'action' => 'Активация Промокода (' . $promo->name . ')',
                'balanceBefore' => $this->user->balance,
                'balanceAfter' => $this->user->balance + $promo->sum
            ]);

            $old_balance = $this->user->balance;
            $this->user->increment('balance', $promo->sum);
            $this->user->increment('wager', $promo->sum * $promo->wager);


            BonuseLog::create([
                'user_id' => $this->user->id,
                'type'    => 'promocode',
                'size'    => $promo->sum
            ]);

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            if (PromocodeActivation::where('promo_id', $promo->id)->where('user_id', $this->user->id)->count() !== 1) {
                DB::rollback();
                return [
                    'error' => true,
                    'message' => 'Вы уже использовали этот код'
                ];
            }


            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Ошибка при активации промокода', [
                'user_id' => $this->user->id ?? null,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'error' => true,
                'message' => 'Подождите'
            ];
        }

        if (!(\Cache::has('user.' . $this->user->id . '.historyBalance'))) {
            \Cache::put('user.' . $this->user->id . '.historyBalance', '[]');
        }

        $hist_balance =    array(
            'user_id' => $this->user->id,
            'type' => 'Активация промокода (' . $promo->name . ')',
            'balance_before' => round($old_balance, 2),
            'balance_after' => round($this->user->balance, 2),
            'date' => date('d.m.Y H:i:s')
        );

        $cashe_hist_user = \Cache::get('user.' . $this->user->id . '.historyBalance');

        $cashe_hist_user = json_decode($cashe_hist_user);
        $cashe_hist_user[] = $hist_balance;
        $cashe_hist_user = json_encode($cashe_hist_user);
        \Cache::put('user.' . $this->user->id . '.historyBalance', $cashe_hist_user);

        DB::commit();
        return [
            'balance' => $this->user->balance,
            'text' => 'Промокод активирован'
        ];
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|min:4|max:10',
            'sum' => 'required|numeric|min:1|max:5',
            'activate' => 'required|integer|min:5|max:100',
            'end_time' => 'required|date_format:Y-m-d H:i:s'
        ]);

        if ($validator->fails()) {
            return [
                'error' => true,
                'message' => $validator->errors()->first()
            ];
        }


        if (Payment::where([['user_id', $this->user->id], ['status', 1]])->sum('sum') < 1000) {
            return [
                'error' => true,
                'message' => 'Необходимо пополнить баланс на 1000 руб'
            ];
        }

        DB::beginTransaction();

        $this->user = User::where('id', $this->user->id)
            ->lockForUpdate()
            ->first();

        $code = $request->code;
        $sum = $request->sum;
        $activate = $request->activate;
        $end_time = $request->end_time;

        $cost = $sum * $activate;

        if ($this->user->balance < $cost) {
            DB::rollback();
            return [
                'error' => true,
                'message' => 'Недостаточно средств'
            ];
        }

        $this->user->decrement('balance', $cost);
        $isExists = Promocode::where('name', $code)->first();

        if ($isExists) {
            DB::rollback();
            return [
                'error' => true,
                'message' => 'Промокод уже существует'
            ];
        }

        Promocode::create([
            'name' => $code,
            'sum' => $sum,
            'activation' => $activate,
            'type' => 'balance',
            'end_time' => $end_time
        ]);

        if (Promocode::where('name', $code)->count() !== 1) {
            DB::rollback();
            return [
                'error' => true,
                'message' => 'Промокод уже существует'
            ];
        }

        if (!(\Cache::has('user.' . $this->user->id . '.historyBalance'))) {
            \Cache::put('user.' . $this->user->id . '.historyBalance', '[]');
        }

        $hist_balance =    array(
            'user_id' => $this->user->id,
            'type' => 'Создание промокода (' . $code . ')',
            'balance_before' => round($this->user->balance + $cost, 2),
            'balance_after' => round($this->user->balance, 2),
            'date' => date('d.m.Y H:i:s')
        );

        $cashe_hist_user = \Cache::get('user.' . $this->user->id . '.historyBalance');

        $cashe_hist_user = json_decode($cashe_hist_user);
        $cashe_hist_user[] = $hist_balance;
        $cashe_hist_user = json_encode($cashe_hist_user);
        \Cache::put('user.' . $this->user->id . '.historyBalance', $cashe_hist_user);

        DB::commit();

        return [
            'success' => true,
            'balance' => $this->user->balance,
            'text' => 'Промокод создан'
        ];
    }
}
