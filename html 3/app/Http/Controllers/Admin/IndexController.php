<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Currencies\CurrencyEnum;
use App\Http\Controllers\Controller;
use App\Models\BonuseLog;
use App\Models\Cashback;
use App\Models\Game;
use App\Models\Payment;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Payments\Enum\PaymentProvidersEnum;
use App\Services\Payments\Facades\PaymentServiceFacade;
use DB;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(Request $r)
    {
        if ($r->user()->admin_role == 'moder') {
            return redirect()->route('admin.deposits');
        }
        $gameStats = Game::select('game')
            ->selectRaw('SUM(win) as total_win, SUM(bet) as total_bet')
            ->groupBy('game')
            ->get()
            ->keyBy('game');

        $bonusStats = BonuseLog::select('type')
            ->selectRaw('SUM(size) as total_size')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $earnedCashback = Cashback::whereNotNull('user_id')->sum('amount');

        $exchangeRate = $this->exchangeRate();

        $cryptobotBalance = (int) PaymentServiceFacade::getBalance(PaymentProvidersEnum::CRYPTOBOT)->getBalance(CurrencyEnum::USDT);
        try {
            $fkBalance = PaymentServiceFacade::getBalance(PaymentProvidersEnum::FK)->getBalance(CurrencyEnum::RUB);
        } catch (\Throwable $throwable) {
            $fkBalance = 0;
        }

        $data = [
            'profitDice' => $gameStats['dice']->total_win ?? 0,
            'amountDice' => $gameStats['dice']->total_bet ?? 0,
            'profitMines' => $gameStats['mines']->total_win ?? 0,
            'amountMines' => $gameStats['mines']->total_bet ?? 0,
            'profitPlinko' => $gameStats['plinko']->total_win ?? 0,
            'amountPlinko' => $gameStats['plinko']->total_bet ?? 0,
            'profitWheel' => $gameStats['wheel']->total_win ?? 0,
            'amountWheel' => $gameStats['wheel']->total_bet ?? 0,
            'profitBubbles' => $gameStats['bubbles']->total_win ?? 0,
            'amountBubbles' => $gameStats['bubbles']->total_bet ?? 0,
            'earnedDaily' => $bonusStats['daily']->total_size ?? 0,
            'earnedPromo' => $bonusStats['promocode']->total_size ?? 0,
            'earnedOneTime' => $bonusStats['onetime']->total_size ?? 0,
            'earnedRef' => $bonusStats['ref']->total_size ?? 0,
            'earnedRepost' => $bonusStats['repost']->total_size ?? 0,
            'earnedCashback' => $earnedCashback,
            'exchangeRate' => $exchangeRate,
            'fkWaletBalanceRub' => $fkBalance ?? 0,
            'cryptobotWaletBalanceUSDT' => $cryptobotBalance,
        ];

        return view('admin.index', $data);
    }

    private function exchangeRate()
    {
        $url = 'https://api.binance.com/api/v3/ticker/price?symbol=USDTRUB';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['price'])) {
            $rubToUsdtRate = $data['price'];
            return $rubToUsdtRate;
        }
    }

    public function getUserByMonth()
    {
        $chart = User::select(DB::raw('DATE_FORMAT(created_at, "%d.%m") as date'), DB::raw('count(*) as count'))
            ->where('is_bot', 0)
            ->whereMonth('created_at', '=', date('m'))
            ->whereYear('created_at', '=', date('Y'))
            ->groupBy('date')
            ->get();

        return $chart;
    }

    public function getUserStatsByMonth()
    {
        // Получаем общее количество пользователей по дням
        $users = User::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'),
            DB::raw('COUNT(*) as total_users')
        )
            ->where('is_bot', 0)
            ->whereMonth('created_at', '=', date('m'))
            ->whereYear('created_at', '=', date('Y'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Получаем пользователей с депозитами по дням (по совпадению даты регистрации и депозита)
        $usersWithDeposit = User::select(
            DB::raw('DATE_FORMAT(users.created_at, "%Y-%m-%d") as date'),
            DB::raw('COUNT(users.id) as users_with_deposit')
        )
            ->join('payments', function ($join) {
                $join->on('users.id', '=', 'payments.user_id')
                    ->whereRaw('DATE(users.created_at) = DATE(payments.created_at)')
                    ->where('payments.status', 1);
            })
            ->where('users.is_bot', 0)
            ->whereMonth('users.created_at', '=', date('m'))
            ->whereYear('users.created_at', '=', date('Y'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Получаем активных пользователей (совершивших депозит) по дням
        $activeUsers = Payment::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'),
            DB::raw('COUNT(DISTINCT user_id) as active_users')
        )
            ->where('status', 1)
            ->whereMonth('created_at', '=', date('m'))
            ->whereYear('created_at', '=', date('Y'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Собираем список всех дат
        $dates = array_unique(array_merge(
            array_keys($users->toArray()),
            array_keys($usersWithDeposit->toArray()),
            array_keys($activeUsers->toArray())
        ));
        sort($dates);

        // Формируем финальный массив данных
        $chartData = [];
        foreach ($dates as $date) {
            $chartData[] = [
                'date' => date('d.m', strtotime($date)),
                'total_users' => $users[$date]->total_users ?? 0,
                'users_with_deposit' => $usersWithDeposit[$date]->users_with_deposit ?? 0,
                'active_users' => $activeUsers[$date]->active_users ?? 0,
            ];
        }

        return response()->json($chartData);
    }


    public function getDepsByMonth()
    {
        $chart = Payment::where('status', 1)
            ->select(DB::raw('DATE_FORMAT(created_at, "%d.%m") as date'), DB::raw('SUM(sum) as sum'))
            ->whereMonth('created_at', '=', date('m'))
            ->whereYear('created_at', '=', date('Y'))
            ->groupBy('date')
            ->get();

        return $chart;
    }

    public function getWithdrawByMonth()
    {
        $exchangeRate = $this->exchangeRate();

        $chart = Withdraw::where([['fake', 0], ['status', 1]])
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%d.%m") as date'),
                DB::raw('ROUND(SUM(CASE WHEN system = "usdt" THEN sum * ' . $exchangeRate . ' ELSE sum END), 2) as sum')
            )
            ->whereMonth('created_at', '=', date('m'))
            ->whereYear('created_at', '=', date('Y'))
            ->groupBy('date')
            ->get();

        return $chart;
    }

    public function getProfitByMonth()
    {
        $deps = $this->getDepsByMonth()->keyBy('date');
        $withdraws = $this->getWithdrawByMonth()->keyBy('date');

        $dates = array_unique(array_merge(array_keys($deps->toArray()), array_keys($withdraws->toArray())));
        $profit = [];

        foreach ($dates as $date) {
            $depSum = $deps->has($date) ? (float)$deps[$date]->sum : 0;
            $withdrawSum = $withdraws->has($date) ? (float)$withdraws[$date]->sum : 0;

            $profit[] = [
                'date' => $date,
                'sum' => round($depSum - $withdrawSum, 2),
            ];
        }

        usort($profit, function ($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        return collect($profit);
    }

    public function getAllStatsByMonth()
    {
        // Получаем депозиты по дням
        $deps = Payment::where('status', 1)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'),
                DB::raw('ROUND(SUM(sum), 2) as sum')
            )
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Получаем выводы по дням
        $withdraws = Withdraw::where('status', 1)
            ->whereMonth('created_at', date('m'))
            ->whereYear('created_at', date('Y'))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'),
                DB::raw('ROUND(SUM(sum), 2) as sum')
            )
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // Объединяем все даты
        $dates = array_unique(array_merge(
            array_keys($deps->toArray()),
            array_keys($withdraws->toArray())
        ));
        sort($dates);

        // Вычисляем профит
        $profit = [];

        foreach ($dates as $date) {
            $depSum = $deps->has($date) ? (float)$deps[$date]->sum : 0;
            $withdrawSum = $withdraws->has($date) ? (float)$withdraws[$date]->sum : 0;

            $profit[] = [
                'date' => date('d.m', strtotime($date)), // для читаемости
                'sum' => round($depSum - $withdrawSum, 2),
            ];

            // Можно добавить если нужно: $totalProfit += ...
        }

        return response()->json([
            'deps' => $deps->map(function ($item) {
                $item->date = date('d.m', strtotime($item->date));
                return $item;
            })->values(),

            'withdraws' => $withdraws->map(function ($item) {
                $item->date = date('d.m', strtotime($item->date));
                return $item;
            })->values(),

            'profits' => $profit,
        ]);
    }


    public function getMerchant()
    {
        $shop_id = $this->config->kassa_id;
        $api_key = $this->config->kassa_key;
        $data = [
            'shopId' => $shop_id,
            'nonce' => time(),
        ];
        ksort($data);
        $sign = hash_hmac('sha256', implode('|', $data), $api_key);
        $data['signature'] = $sign;

        $request = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.freekassa.ru/v1/balance');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FAILONERROR, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        $result = trim(curl_exec($ch));
        curl_close($ch);

        $res = json_decode($result, true);
        return (isset($res['type'])) ? ($res['type'] == 'error') ? $res['message'] : $res['balance'][0]['value'] : $res['msg'];
    }

    public function getVK(Request $r)
    {
        $id = $r->vk_id;

        $info = file_get_contents("https://vk.com/foaf.php?id={$id}");
        $data = preg_match('|ya:created dc:date="(.*?)"|si', $info, $arr);

        return date("d.m.Y H:i:s", strtotime($arr[1]));
    }

    public function getCountry(Request $r)
    {
        $ip = $r->user_ip;

        $curl = curl_init("http://ip-api.com/json/{$ip}?lang=ru");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $response = curl_exec($curl);
        curl_close($curl);

        $content = json_decode($response, true);

        return $content['status'] == 'fail' ? $content['message'] : $content['city'];
    }
}
