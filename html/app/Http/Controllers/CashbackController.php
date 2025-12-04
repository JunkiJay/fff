<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Cashback;
use App\Models\Payment;
use App\Models\User;
use App\Models\Withdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CashbackController extends Controller
{
    public function cashback(Request $request)
    {
        $user = User::find($request->user()->id);
        if (!$user) return 'User not found';
        $payments = Payment::where('user_id', $user->id)->where('status', 1)->sum('sum');
        $withdraws = Withdraw::where('user_id', $user->id)->where('status', 1)->sum('sum');
        $took_before = Cashback::where("user_id", $user->id)->sum('amount');
        $cashback_amount = ($payments - $withdraws) * 0.1 - $took_before;
        return $cashback_amount;
    }

    public function getCashback(Request $request)
    {
        $user = User::find($request->user()->id);
        if (!$user) return ['error' => 'User not found'];

        // Устанавливаем московское время
        $moscowTime = Carbon::now('Europe/Moscow');

        // Проверка на пятницу
        if ($moscowTime->dayOfWeek !== Carbon::FRIDAY) {
            return ['error' => 'Кэшбек можно получить только в пятницу'];
        }

        $cashback_amount = $this->cashback($request);

        if ($user->balance > 1) {
            return ['error' => 'На балансе должно быть не больше 1 рубля'];
        }

        if ($cashback_amount < 1) {
            return ['error' => 'Вывод кешбека доступен от 1 рубля'];
        }

        // Проверка на получение кэшбэка в текущую пятницу
        $startOfWeek = $moscowTime->copy()->startOfWeek(Carbon::FRIDAY);
        $endOfWeek = $moscowTime->copy()->endOfWeek(Carbon::FRIDAY);

        DB::beginTransaction();

        try {
            $lastCashback = Cashback::where('user_id', $user->id)
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->orderBy('created_at', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastCashback) {
                DB::rollBack();
                return ['error' => 'Вы уже получили кэшбек на этой неделе'];
            }

            // Создаем запись о кэшбэке
            Cashback::create([
                'user_id'    => $user->id,
                'amount'     => $cashback_amount,
                'created_at' => $moscowTime
            ]);

            Action::create([
                'user_id' => $user->id,
                'action' => 'Кешбек (+' . $cashback_amount . ')',
                'balanceBefore' => $user->balance,
                'balanceAfter' => $user->balance - $cashback_amount
            ]);

            // Обновляем баланс пользователя
            $user->increment('balance', $cashback_amount);
            $user->increment('wager', $cashback_amount * 3);

            // Обновляем историю баланса
            $hist_balance = [
                'user_id' => $user->id,
                'type' => 'cashback (+' . $cashback_amount . ')',
                'balance_before' => $user->balance - $cashback_amount,
                'balance_after' => $user->balance,
                'date' => $moscowTime->format('d.m.Y H:i:s')
            ];

            $cacheKey = 'user.' . $user->id . '.historyBalance';
            $cache_hist_user = Cache::get($cacheKey, '[]');
            $cache_hist_user = json_decode($cache_hist_user, true);
            $cache_hist_user[] = $hist_balance;
            Cache::put($cacheKey, json_encode($cache_hist_user));

            DB::commit();
        } catch (\Throwable $th) {
            \Log::error($th);
            DB::rollBack();
            return ['error' => 'Произошла ошибка при обработке кэшбека'];
        }

        return ['balance' => $user->balance, 'text' => 'Зачисление кешбека'];
    }
}
