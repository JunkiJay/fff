<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProviderResource;
use App\Models\Action;
use App\Models\MobuleSlot;
use App\Models\SlotSession;
use App\Models\User;
use App\Services\Slots\Facades\SlotServiceFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class MobuleSlotController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = 'https://pp.rmobule.games';
        // try {
        //     $this->apiUrl = env('MOBULE_API_URL', 'https://pp.casinomobule.com');
        //     $ip = $this->getIp();
        //     $location = GeoIP::getLocation($ip);
        
        //     $country = null;
        //     if (is_object($location) && property_exists($location, 'iso_code')) {
        //         $country = $location->iso_code;
        //     }
        //     Log::debug("Country: " . ($country ?? 'undefined'));
        
        //     if ($country === 'RU') {
        //         $this->apiUrl = 'https://pp.rmobule.games';
        //     }
        // } catch (\Throwable $e) {
        //     Log::error('Ошибка в __construct MobuleSlotController: ' . $e->getMessage(), [
        //         'exception' => $e,
        //         'trace' => $e->getTraceAsString(),
        //     ]);
        //     $this->apiUrl = 'https://pp.rmobule.games';
        //     // Не выбрасываем ошибку дальше, чтобы не сломать весь фреймворк
        // }
    }

    public function fetchAndStore()
    {
        $response = Http::get("{$this->apiUrl}/games.list");

        if (!$response->successful()) {
            return response()->json(['message' => 'Ошибка при запросе к API'], 500);
        }

        $data = $response->json()['response'];

        $this->storeSlots($data);

        return response()->json(['message' => 'Данные успешно сохранены!'], 201);
    }

    private function storeSlots($slots)
    {
        foreach ($slots as $slot) {
            if ($slot['is_enabled']) {
                MobuleSlot::updateOrCreate([
                    'id' => $slot['id'],
                ], [
                    'alias' => $slot['alias'],
                    'group_alias' => $slot['group_alias'],
                    'title' => $slot['title'],
                    'provider' => $slot['provider'],
                    'is_enabled' => $slot['is_enabled'],
                    'is_freerounds_enabled' => $slot['is_freerounds_enabled'],
                    'desktop_enabled' => $slot['desktop_enabled'],
                    'mobile_enabled' => $slot['mobile_enabled'],
                    'base_total_bet' => $slot['meta']['base_total_bet'] ?? 0,
                    'max_bet_level' => $slot['meta']['max_bet_level'] ?? 0,
                    'show' => 1,
                ]);
            }
        }
    }

    public function getSlots(Request $request)
    {
        try {
            $request->validate([
                'provider' => 'nullable|string',
                'search' => 'nullable|string',
                'per_page' => 'nullable|integer',
            ]);

            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 24);

            $query = MobuleSlot::query()
                ->where('is_enabled', 1)
                ->where('show', 1)
                ->orderBy('id', 'desc');

            if ($request->filled('provider') && $request->provider !== 'all') {
                $query->where('provider', $request->provider);
            }

            if ($request->filled('search')) {
                $query->where('title', 'LIKE', '%' . $request->search . '%');
            }

            $slots = $query->paginate($perPage, ['*'], 'page', $page);

            foreach ($slots->items() as $slot) {
                $slot->provider_url = 'mobule';
                $slot->preview_url = SlotServiceFacade::getImage($slot) ?? $this->getSlotImageUrl($slot->title);
            }

            return response()->json($slots);
        } catch (\Throwable $e) {
            \Log::error('Ошибка в getSlots: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json(['message' => 'Внутренняя ошибка сервера'], 500);
        }
    }


    private function getSlotImageUrl($alias)
    {
        $cleanAlias = str_replace(["$", "'", "-", "'", "’", "™", ":", " "], "", $alias);
        $basePath = public_path('assets/image/slots/');

        $jpgPath = $basePath . $cleanAlias . '.jpg';
        if (file_exists($jpgPath)) {
            return '/assets/image/slots/' . $cleanAlias . '.jpg';
        }

        $pngPath = $basePath . $cleanAlias . '.png';
        if (file_exists($pngPath)) {
            return '/assets/image/slots/' . $cleanAlias . '.png';
        }

        return '/images/soon.png';
    }

    public function loadSlot(Request $request)
    {
        try {
            // Валидация slot_id
            $request->validate([
                'slot_id' => 'required|integer|min:1',
            ]);
            
            $slot = MobuleSlot::where('id', $request->slot_id)->first();
            $user = $request->user() ?? \Illuminate\Support\Facades\Auth::user();
        
            if (!$slot || $slot->show == 0) {
                return response()->json(['message' => 'Игра не найдена'], 404);
            }
        
            if (!$user) {
                return response()->json(['message' => 'Авторизуйтесь'], 401);
            }
        
            if ($user->is_youtuber) {
                return response()->json(['message' => 'Ваша роль запрещает вам это действие'], 401);
            }

            // Генерируем auth_token только если его нет (можно оставить для других целей)
            if ($user->auth_token == null) {
                $user->auth_token = Str::random(12);
                $user->save();
            }
        
            $user->current_id = $slot->id;
            $user->save();
        
            // Создаём игровую сессию
            try {
                $gameSession = SlotSession::create([
                    'user_id' => $user->id,
                    'game_id' => $slot->id,
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка создания игровой сессии: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'slot_id' => $slot->id,
                    'exception' => $e,
                ]);
                return response()->json(['message' => 'Ошибка создания игровой сессии'], 500);
            }
        
            $partner = "stimule2";
            $currency = "RUB";
            $mobile = $request->input('mobile', false);
            $mobile = $mobile ? 'true' : 'false';
            $lang = "ru";
            $lobbyurl = config('app.url');
            
            // Проверка что необходимые поля слота заполнены
            if (empty($slot->provider) || empty($slot->alias)) {
                Log::error('Слот не имеет provider или alias', [
                    'slot_id' => $slot->id,
                    'provider' => $slot->provider ?? 'null',
                    'alias' => $slot->alias ?? 'null',
                ]);
                return response()->json(['message' => 'Ошибка конфигурации игры'], 500);
            }
            
            if (empty($lobbyurl)) {
                Log::error('APP_URL не установлен');
                return response()->json(['message' => 'Ошибка конфигурации сервера'], 500);
            }
    
            // Правильно кодируем URL параметры
            $params = [
                'partner.alias' => $partner,
                'partner.session' => $gameSession->id,
                'game.provider' => $slot->provider,
                'game.alias' => $slot->alias,
                'lang' => $lang,
                'lobby_url' => $lobbyurl,
                'currency' => $currency,
                'mobile' => $mobile,
            ];
            
            $link = $this->apiUrl . "/games.start?" . http_build_query($params);
            // $demo_link = $this->apiUrl . "/games.startDemo?partner.alias=" . $partner . "&partner.session={$gameSession->id}&game.provider={$slot->provider}&game.alias={$slot->alias}&lang={$lang}&lobby_url={$lobbyurl}&currency={$currency}&mobile={$mobile}";
        
            return response()->json(['title' => $slot->title, 'link' => $link]);
        } catch (\Throwable $e) {
            Log::error('Ошибка в loadSlot: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'slot_id' => $request->slot_id ?? null,
            ]);
            return response()->json(['message' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    public function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }


    public function callback($method, Request $request)
    {
        try {
            // Логируем все данные для отладки
            Log::info('Module callback: ', [$method, $request->all()]);

            $params = $request->except(['sign', 'meta']);
            $secret = env('MOBULE_API_KEY');

            $sign = $this->generateSign($params, $request->get('partner.alias'), $secret, $method);

            switch ($method) {
                case 'trx.cancel':
                    return $this->trxCancel($request);
                case 'trx.complete':
                    return $this->trxComplete($request);
                case 'check.session':
                    return $this->checkSession($request);
                case 'check.balance':
                    return $this->checkBalance($request);
                case 'withdraw.bet':
                    return $this->userBet($request);
                case 'deposit.win':
                    return $this->userWin($request);
                default:
                    throw new \Exception("Unknown method");
            }
        } catch (\Throwable $e) {
            Log::error('Ошибка в callback: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
                'method' => $method,
            ]);
            return response()->json(['message' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    private function generateSign($params, $partner, $secret, $method)
    {
        $joined = array_filter(array_keys($params), function ($name) {
            return !str_starts_with($name, "partner.");
        });
        sort($joined);
        $joined = array_map(function ($name) use ($params) {
            $value = $params[$name];
            if (is_array($value)) {
                $value = json_encode($value);
            }
            return $name . "=" . $value;
        }, $joined);
        $joined = implode("&", $joined);

        return md5($joined . "&" . $method . "&" . $partner . "&" . $secret);
    }

    private function trxCancel($data)
    {
        return response()->json(['status' => 200]);
    }

    private function trxComplete($data)
    {
        return response()->json(['status' => 200]);
    }

    private function checkSession($data)
    {
        if (!$data->session) {
            return response()->json(['status' => 404, 'method' => 'check.session', 'message' => 'Unknown session']);
        }
        $slotSession = SlotSession::find($data->session);
        if (!$slotSession) {
            return response()->json(['status' => 404, 'method' => 'check.session', 'message' => 'Unknown session']);
        }
        $user = User::find($slotSession->user_id);
        if (!$user) {
            return response()->json(['status' => 404, 'method' => 'check.session', 'message' => 'Unknown user']);
        }
        return response()->json(['status' => 200, 'method' => 'check.session', 'response' => ['id_player' => $user->id, 'id_group' => 'default', 'balance' => round($user->balance * 100)]]);
    }

    private function checkBalance($data)
    {
        if (!$data->session) {
            return response()->json(['status' => 404, 'method' => 'check.balance', 'message' => 'Unknown session']);
        }
        $slotSession = SlotSession::find($data->session);
        if (!$slotSession) {
            return response()->json(['status' => 404, 'method' => 'check.balance', 'message' => 'Unknown session']);
        }
        $user = User::find($slotSession->user_id);
        if (!$user) {
            return response()->json(['status' => 404, 'method' => 'check.balance', 'message' => 'Unknown user']);
        }
        return response()->json(['status' => 200, 'method' => 'check.balance', 'response' => ['currency' => 'RUB', 'balance' => round($user->balance * 100)]]);
    }

    public function userBet($data)
    {
        if (!$data->session) {
            return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Unknown session']);
        }
        $slotSession = SlotSession::find($data->session);
        if (!$slotSession) {
            return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Unknown session']);
        }
        $user = User::find($slotSession->user_id);
        if (!$user) {
            return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Unknown user']);
        }
        $amount = $data->amount / 100;
        if ($user->balance < ($amount)) {
            return response()->json(['status' => 404, 'method' => 'withdraw.bet', 'message' => 'Fail balance']);
        }

        $balance = $this->userDebitUpdateAmount($user->id, $amount);
        return response()->json(['status' => 200, 'method' => 'withdraw.bet', 'response' => ['currency' => 'RUB', 'balance' => round($balance * 100)]]);
    }

    public function userWin($data)
    {
        if (!$data->session) {
            return response()->json(['status' => 404, 'method' => 'deposit.win', 'message' => 'Unknown session']);
        }
        $slotSession = SlotSession::find($data->session);
        if (!$slotSession) {
            return response()->json(['status' => 404, 'method' => 'deposit.win', 'message' => 'Unknown session']);
        }
        $user = User::find($slotSession->user_id);
        if (!$user) {
            return response()->json(['status' => 404, 'method' => 'deposit.win', 'message' => 'Unknown user']);
        }
        $amount = $data->amount / 100;
        $balance = $this->userCreditUpdateAmount($user->id, $amount);
        return response()->json(['status' => 200, 'method' => 'deposit.win', 'response' => ['currency' => 'RUB', 'balance' => round($balance * 100)]]);
    }

    public function providers()
    {
        try {
            $desiredProviders = [
                'pragmatic',
                'nolimit',
                "netent",
                "redtiger",
                "evoplay",
                "amatic",
                "relax",
                "spribe",
                "spinomenal",
                "hacksaw",
                "playson",
                "3oaks",
                "pgsoft"
            ];
            
            $providers = MobuleSlot::query()
                ->where('show', 1)
                ->whereIn('provider', $desiredProviders)
                ->select('provider as title')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('provider')
                ->get();
            
            // Если нет провайдеров, возвращаем пустой массив
            if ($providers->isEmpty()) {
                return response()->json([]);
            }
            
            // Преобразуем в массив вручную для большей надежности
            $result = $providers->map(function ($provider) {
                // После select('provider as title') поле доступно только как title
                $title = $provider->title ?? 'Unknown';
                return [
                    'title' => $title,
                    'img' => $title . '.svg',
                    'count' => (int)($provider->count ?? 0)
                ];
            });
            
            return response()->json($result->values()->all());
        } catch (\Throwable $e) {
            \Log::error('Ошибка в providers: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            // Возвращаем пустой массив вместо ошибки, чтобы фронтенд не сломался
            return response()->json([]);
        }
    }

    public function show($id)
    {
        $slot = MobuleSlot::where('id', $id)->first();

        if (!$slot) {
            return response()->json(['error' => true, 'message' => 'Данный слот не найден'], 404);
        }

        return response()->json(['success' => true, 'slot' => $slot], 200);
    }

    public function userCreditUpdateAmount($user_id, $credit_amount)
    {
        $user = User::where('id', $user_id)->first();

        if ($credit_amount > 0) {
            Action::create([
                'user_id' => $user->id,
                'action' => 'slot (+' . $credit_amount . ')',
                'balanceBefore' => $user->balance,
                'balanceAfter' => $user->balance + $credit_amount
            ]);

            if (!(Cache::has('user.' . $user->id . '.historyBalance'))) {
                Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'slot (+' . $credit_amount . ')',
                'balance_before' => $user->balance,
                'balance_after' => $user->balance + $credit_amount,
                'date' => date('d.m.Y H:i:s')
            );
            $user->increment('slots', $credit_amount);

            $cashe_hist_user = Cache::get('user.' . $user->id . '.historyBalance');
            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $slot = MobuleSlot::where('id', $user->current_id)->first();
            if (!$user->is_youtuber && $slot && $slot->title) {
                Redis::publish('slotsHistory', json_encode([
                    'id' => $slot->id,
                    'game_id' => $user->current_id,
                    'image' => SlotServiceFacade::getImage($slot) ?? '/assets/image/slots/' . implode('', explode(' ', $slot->title)) . '.jpg',
                    'slot_name' => $slot->title,
                    'username' => $user->username,
                    'coef' => number_format(($credit_amount / $user->current_bet), 2),
                    'win' => $credit_amount
                ]));
            }
        }

        $user->balance += $credit_amount;
        $user->save();
        $user->refresh();

        return $user->balance;
    }

    public function userDebitUpdateAmount($user_id, $debit_amount)
    {
        $user = User::where('id', $user_id)->first();
        Action::create([
            'user_id' => $user->id,
            'action' => 'slot (-' . $debit_amount . ')',
            'balanceBefore' => $user->balance,
            'balanceAfter' => $user->balance - $debit_amount
        ]);

        if($user->slots_wager){
            if ($user->slots_wager > 0) {
                $user->slots_wager -= $debit_amount;
            }
            if ($user->slots_wager < 0) $user->slots_wager = 0;
        }

        if ($user->wager > 0) {
            $user->wager -= $debit_amount;
        }

        if ($user->wager < 0) {
            $user->wager = 0;
        }

        $user->balance -= $debit_amount;
        $user->current_bet = $debit_amount;
        $user->decrement('slots', $debit_amount);
        $user->save();
        $user->refresh();

        return $user->balance;
    }
}
