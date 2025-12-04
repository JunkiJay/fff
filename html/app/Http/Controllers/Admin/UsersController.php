<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Payment;
use App\Models\User;
use App\Models\Withdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Log;

class UsersController extends Controller
{
    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function index(Request $r)
    {
        if ($r->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }
        return view('admin.users.index');
    }

    public function edit($id, Request $r)
    {
        if ($r->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }

        $user = User::query()->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Пользователь не найден!');
        }

        // $cashe_hist_user = \Cache::get('user.' . $id . '.historyBalance') ?? '[]';
        // $cashe_hist_user = json_decode($cashe_hist_user);

        // $logs = $cashe_hist_user;

        $logs = Action::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Получаем даты из запроса
        $startDate = $r->input('start_date');
        $endDate = $r->input('end_date');

        // Вызываем функцию getRef с параметрами даты
        $refData = $this->getRef($user, $startDate, $endDate);

        return view('admin.users.edit', compact('user', 'logs', 'refData'));
    }

    public function getRefOld($user, $startDate = null, $endDate = null)
    {

        $refsQuery = User::where('referral_use', $user->id);

        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);
            $refsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Получаем рефералов
        $refs = $refsQuery->get();

        $refsRaw = User::where('referral_use', $user->id)->get();

        $totalEarnings = 0;
        $first_deps = 0;
        $first_deps_sum = 0;
        $ref_deps_total = 0;
        $withdraw_total = 0;
        Log::debug(json_encode($refsRaw));
        foreach ($refsRaw as $ref) {
            Log::debug('ref stat for ' . $ref->id);


            $paymentsQuery = Payment::where('user_id', $ref->id)
                ->where('status', 1);

            $withdrawsQuery = Withdraw::where('user_id', $ref->id)
                ->where('status', 1);

            if ($startDate && $endDate) {
                Log::debug('searching transfers in a range ' . $startDate . ' - ' . $endDate);
                $startDate = Carbon::parse($startDate);
                $endDate = Carbon::parse($endDate);
                $paymentsQuery->whereBetween('created_at', [$startDate, $endDate]);
                $withdrawsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $earnings = $paymentsQuery->sum('sum');
            $withdraws = $withdrawsQuery->sum('sum');

            Log::debug('rsult for ' . $ref->id . ' earnings: ' . $earnings . ' withdraws: ' . $withdraws);

            $totalEarnings  += $earnings;
            $withdraw_total += $withdraws;
            $ref_deps_total += $paymentsQuery->count();

            if ($earnings > 0) {
                $first_deps += 1;
                $first_dep = $paymentsQuery->orderBy('id', 'desc')->first();
                if ($first_dep) {
                    $first_deps_sum += $first_dep->sum;
                }
            }
        }

        $clean = $first_deps_sum * 0.9 - $withdraw_total;

        return [
            count($refs),
            $first_deps,
            $first_deps_sum,
            $ref_deps_total,
            $totalEarnings,
            $withdraw_total,
            $clean
        ];
    }

    public function getRef($user, $startDate = null, $endDate = null)
    {
        $refsQuery = User::where('referral_use', $user->id);

        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            $refsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        $refs = $refsQuery->get();

        $totalEarnings = 0;
        $first_deps = 0;
        $first_deps_sum = 0;
        $ref_deps_total = 0;
        $withdraw_total = 0;

        foreach ($refs as $ref) {
            $paymentsQuery = Payment::where('user_id', $ref->id)->where('status', 1);
            $withdrawsQuery = Withdraw::where('user_id', $ref->id)->where('status', 1);

            if ($startDate && $endDate) {
                $paymentsQuery->whereBetween('created_at', [$startDate, $endDate]);
                $withdrawsQuery->whereBetween('created_at', [$startDate, $endDate]);
            }

            $earnings = (clone $paymentsQuery)->sum('sum');
            $withdraws = (clone $withdrawsQuery)->sum('sum');
            $depositCount = (clone $paymentsQuery)->count();
            $firstDep = (clone $paymentsQuery)->orderBy('id', 'asc')->first();

            $totalEarnings += $earnings;
            $withdraw_total += $withdraws;
            $ref_deps_total += $depositCount;

            if ($firstDep) {
                $first_deps += 1;
                $first_deps_sum += $firstDep->sum;
            }
        }

        $clean = $first_deps_sum * 0.9 - $withdraw_total;

        return [
            $refs->count(),      // Общее кол-во рефералов
            $first_deps,         // Кол-во тех, кто сделал 1-й депозит
            $first_deps_sum,     // Сумма первых депозитов
            $ref_deps_total,     // Общее кол-во депозитов
            $totalEarnings,      // Общая сумма депозитов
            $withdraw_total,     // Общая сумма выводов
            round($clean, 2),    // "Чистая прибыль"
        ];
    }

    public function editPost($id, Request $r)
    {
        $user = User::query()->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'Пользователь не найден!');
        }

        if ($user->password !== $r->get('password')) {
            $user->update([
                'password' => hash('sha256', $r->get('password'))
            ]);
        }

        User::query()->find($id)->update($r->only([
            'username',
            'balance',
            'ban',
            'limit_payment',
            'wager',
            'slots_wager',
            'auto_withdraw',
            'wager_status',
            'ref_1_lvl',
            'ref_2_lvl',
            'ref_3_lvl',
            'is_admin',
            'is_youtuber',
            'admin_role',
            'is_worker',
        ]));
        return redirect('/admin/users/edit/' . $id)->with('success', 'Данные пользователя обновлены!');
    }

    public function delete($id)
    {
        User::query()->find($id)->delete();

        return redirect()->back()->with('success', 'Пользователь удален');
    }

    public function checker(Request $request)
    {
        $user = User::find($request->user_id);

        $multi = User::query()
            ->orWhere('used_ip', $user->used_ip)
            ->orWhere('created_ip', $user->created_ip)
            ->orWhere('fingerprint', $user->fingerprint)
            ->get();

        return [
            'user' => $user,
            'list' => collect($multi)->where('id', '!=', $user->id)->values()
        ];
    }

    public function createFake($type, $id)
    {
        $user = User::query()->find($id);
        if (!$user) {
            return redirect()->back()->with('error', 'Пользователь не найден!');
        }

        return view('admin.users.create' . $type, compact('user'));
    }

    public function addFake($type, $id, Request $r)
    {
        $user = User::query()->find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'Пользователь не найден!');
        }

        if ($type == 'Payout') {
            $system = $r->system;
            $wallet = $r->wallet;
            $amount = $r->amount;
            $status = $r->status;

            if (!$system) {
                return redirect()->back()->with('error', 'Выберите платежную систему');
            }

            if (!$wallet) {
                return redirect()->back()->with('error', 'Введите кошелек корректно');
            }

            if (!$amount || !is_numeric($amount) || $amount < 1) {
                return redirect()->back()->with('error', 'Введите сумму корректно');
            }

            if (!$status) {
                return redirect()->back()->with('error', 'Выберите статус выплаты');
            }

            Withdraw::query()->create([
                'user_id' => $user->id,
                'sum' => $amount,
                'sumWithCom' => $amount,
                'wallet' => $wallet,
                'system' => $system,
                'status' => $status,
                'fake' => 1
            ]);
        }

        if ($type == 'Pay') {
            $amount = $r->amount;
            $add = $r->add;

            if (!$amount || !is_numeric($amount) || $amount < 1) {
                return redirect()->back()->with('error', 'Введите сумму корректно');
            }

            if (!$add) {
                return redirect()->back()->with('error', 'Заполните все поля');
            }

            if ($add == 'y') {
                $user->balance += $amount;
                $user->save();
            }

            Payment::query()->create([
                'user_id' => $user->id,
                'sum' => $amount,
                'status' => 1,
                'fake' => 1
            ]);
        }
        return redirect()->back()->with('success', $type == 'Payout' ? 'Выплата успешно добавлена' : 'Пополнение успешно добавлено');
    }
}
