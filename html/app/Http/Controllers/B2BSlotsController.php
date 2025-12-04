<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProviderResource;
use App\Models\Action;
use App\Models\B2bSlot;
use App\Models\MobuleSlot;
use App\Models\Payment;
use App\Models\Promocode;
use App\Models\PromocodeActivation;
use App\Models\SlotSession;
use App\Models\User;
use App\Services\Slots\Facades\SlotServiceFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class B2BSlotsController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        // Новый домен провайдера B2B слотов
        $this->apiUrl = env('B2B_API_URL', 'https://icdnchannel.com');
    }

    public function fetchAndStore()
    {
        $response = Http::get("{$this->apiUrl}/frontendsrv/apihandler.api?cmd=%7B%22api%22:%22ls-games-by-operator-id-get%22,%22operator_id%22:%2240198%22%7D");

        if (!$response->successful()) {
            return response()->json(['message' => 'Ошибка при запросе к API'], 500);
        }

        $data = $response->json()['locator'];
        $this->storeSlots($data['ico_baseurl'], $data['groups']);

        return response()->json(['message' => 'Данные успешно сохранены!'], 201);
    }

    private function storeSlots($ico_baseurl, $groups)
    {
        $maxMobuleSlotId  = MobuleSlot::max('id');
        $nextId = $maxMobuleSlotId + 1;
        foreach ($groups as $group) {
            foreach ($group['games'] as $game) {
                $icon_url = isset($game['icons'][0]['ic_name']) ? "{$this->apiUrl}{$ico_baseurl}{$game['icons'][0]['ic_name']}" : null;
                B2bSlot::updateOrCreate(
                    [
                        'gm_bk_id' => $game['gm_bk_id'],
                    ],
                    [
                        'id' => $nextId,
                        'gr_title' => $group['gr_title'],
                        'gr_id' => $group['gr_id'],
                        'gm_is_board' => $game['gm_is_board'],
                        'gm_m_w' => $game['gm_m_w'],
                        'gm_ln' => $game['gm_ln'],
                        'gm_is_copy' => $game['gm_is_copy'],
                        'gm_url' => $game['gm_url'],
                        'gm_is_retro' => $game['gm_is_retro'],
                        'gm_d_w' => $game['gm_d_w'],
                        'icon_url' => $icon_url,
                        'show' => 1,
                    ]
                );
                $nextId++;
            }
        }
    }

    public function getSlots(Request $request)
    {
        $request->validate([
            'provider' => 'nullable|string',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer',
        ]);

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 24);

        $query = B2bSlot::query()
            ->where('show', 1)
            ->orderBy('id', 'desc'); 

        if ($request->filled('provider') && $request->provider !== 'all') {
            $query->where('gr_title', $request->provider);
        }

        if ($request->filled('search')) {
            $query->where('gm_url', 'LIKE', '%' . $request->search . '%');
        }

        $slots = $query->paginate($perPage, ['*'], 'page', $page);

        foreach ($slots->items() as $slot) {
            $slot->provider_url = 'b2b';
            $image = SlotServiceFacade::getImage($slot);
            $slot->preview_url = $image ?? $this->getSlotImageUrl($slot->gm_url);
        }

        return response()->json($slots);
    }


    private function getSlotImageUrl($alias)
    {
        $cleanAlias = str_replace('.game', "", $alias);
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
        // Мягкая валидация slot_id + логирование, вместо жёсткого 422
        \Log::info('B2B loadSlot request', [
            'data' => $request->all(),
            'user_id' => optional($request->user())->id,
            'ip' => $request->ip(),
        ]);

        $slotId = $request->input('slot_id');
        if (!$slotId || !is_numeric($slotId) || (int)$slotId <= 0) {
            \Log::warning('B2B loadSlot invalid slot_id', ['slot_id' => $slotId]);
            return response()->json([
                'error' => true,
                'message' => 'Неверный ID слота',
            ], 400);
        }
        $slotId = (int)$slotId;
        
        $user = $request->user() ?? Auth::user();
        if (!$user) {
            return response()->json(['error' => true, 'message' => 'Вы не авторизованы'], 401);
        }

        if ($user->ban) {
            return response()->json(['error' => true, 'message' => 'Ваш аккаунт заблокирован'], 403);
        }

        $slot = B2bSlot::where('id', $slotId)->first();

        $psum = Payment::where([['created_at', '>=', \Carbon\Carbon::today()->subDays(7)], ['user_id', $user->id], ['status', 1]])->sum('sum');
      
        if (!$slot || $slot->show == 0) {
            return response()->json(['error' => true, 'message' => 'Данный слот не найден'], 404);
        }

        if (is_null($user->auth_token)) {
            $user->auth_token = bin2hex(random_bytes(20));
        }

        $user->current_id = $slot->id;
        $user->save();

        $cur = false;

        $url = 'https://int4.datachannel.cloud/.gambleApi';
        $queryParams = json_encode([
            'api' => 'plugin',
            'data' => [
                'name' => 'control',
                'action' => 'getActiveGamesByUser',
                'data' => [
                    'provider' => '40115',
                    'userId' => strval($user->id)
                ]
            ]
        ]);

        // Формируем URL с параметрами запроса
        $urlWithParams = $url . '?' . $queryParams;

        // Инициализируем cURL сессию
        $curl = curl_init();

        // Устанавливаем опции cURL
        curl_setopt_array($curl, [
            CURLOPT_URL => $urlWithParams,
            CURLOPT_RETURNTRANSFER => true, // Возвращать результат в виде строки
        ]);

        // Выполняем запрос
        $response = curl_exec($curl);

        // Проверяем успешность запроса
        if ($response) {
            // Получаем тело ответа в виде ассоциативного массива
            $responseData = json_decode($response);

            // Проверяем наличие данных в games
            if (isset($responseData->games) && !empty($responseData->games)) {
                // Данные в games есть
                $cur = $responseData->games[0]->currency;
                // Обрабатываем данные
            } else {
                // Данные в games отсутствуют
            }
        } else {
            // Обработка ошибки запроса
        }

        $promocodeActivation = PromocodeActivation::where('user_id', $user->id)
            ->where('status', 0)
            ->latest('created_at')
            ->first();

        if ($promocodeActivation) {
            $currency = 'RUB';
        }

        $gameSession = SlotSession::create([
            'user_id' => $user->id,
            'game_id' => $slot->id,
            'created_at' => now(),
        ]);

        // Специальный URL выхода из игры: при открытии в iframe он выбрасывает пользователя на основной сайт
        $homeUrl = config('app.url') . '/game-exit';
        
        if (!$cur) {
            if ($user->is_youtuber) {
                $link = "https://icdnchannel.com/gamesbycode/" . $slot->gm_bk_id . ".gamecode?operator_id=" . ($user->is_youtuber ? '40114' : '40115') . "&language=ru&user_id={$user->id}&auth_token={$user->auth_token}&currency=RUB&desktop_interface=1&home_url=" . urlencode($homeUrl);
            } else {
                if ($psum < 100) {
                    $link = "https://icdnchannel.com/gamesbycode/" . $slot->gm_bk_id . ".gamecode?operator_id=40115&language=ru&user_id={$user->id}&auth_token={$user->auth_token}&currency=RUBB&desktop_interface=1&home_url=" . urlencode($homeUrl);
                } else {
                    $link = "https://icdnchannel.com/gamesbycode/" . $slot->gm_bk_id . ".gamecode?operator_id=" . ($user->is_youtuber ? '40114' : '40115') . "&language=ru&user_id={$user->id}&auth_token={$user->auth_token}&currency=RUB&desktop_interface=1&home_url=" . urlencode($homeUrl);
                }
            }
        } else {
            if ($cur == 'RUBB' ) {
                $link = "https://icdnchannel.com/gamesbycode/" . $slot->gm_bk_id . ".gamecode?operator_id=40115&language=ru&user_id={$user->id}&auth_token={$user->auth_token}&currency=RUBB&desktop_interface=1&home_url=" . urlencode($homeUrl);
            } else {
                $link = "https://icdnchannel.com/gamesbycode/" . $slot->gm_bk_id . ".gamecode?operator_id=" . ($user->is_youtuber ? '40114' : '40115') . "&language=ru&user_id={$user->id}&auth_token={$user->auth_token}&currency=RUB&desktop_interface=1&home_url=" . urlencode($homeUrl);
            }
        }

        // $demo_link = "{$this->apiUrl}/gamesbycode/{$slot->gm_bk_id}.gamecode?operator_id=0&language=ru&user_id={$user->id}&auth_token={$user->auth_token}&currency=RUB&desktop_interface=1&home_url=" . config('app.url') . "/slots";
       
        return response()->json(['title' => $slot->gm_url, 'link' => $link]);
    }

    public function callback(Request $request)
    {
        header("Connection: close");
        $data = file_get_contents('php://input');
        $requestContent = json_decode($data);
        Log::info('B2B callback: ', [$requestContent, $request->all()]);

        try {
            switch ($requestContent->api) {
                case 'do-auth-user-ingame':
                    $response = $this->auth($requestContent);
                    break;
                case 'do-debit-user-ingame':
                    $response = $this->debit($requestContent);
                    break;
                case 'do-credit-user-ingame':
                    $response = $this->credit($requestContent);
                    break;
                case 'do-get-features-user-ingame':
                    $response = $this->getFeatures($requestContent);
                    break;
                case 'do-activate-features-user-ingame':
                    $response = $this->activateFeatures($requestContent);
                    break;
                case 'do-update-features-user-ingame':
                    $response = $this->updateFeatures($requestContent);
                    break;
                case 'do-end-features-user-ingame':
                    $response = $this->endFeatures($requestContent);
                    break;
                default:
                    throw new \Exception("Unknown API");
            }
            echo json_encode($response);
        } catch (\Exception $e) {
            $this->handleException($e, $requestContent->api);
        }
    }


    private function handleException(\Exception $e, $api)
    {
        $response = (object)[
            'answer' => (object)[
                'error_code' => 1,
                'error_description' => $e->getMessage(),
                'timestamp' => (string)time(),
            ],
            'api' => $api,
            'success' => true,
        ];
        echo json_encode($response);
    }

    private function auth($requestContent)
    {
        $user = User::find($requestContent->data->user_id);
        $game_token = md5(time() . mt_rand(1, 1000000));
        $user->update(['remember_token' => $game_token]);

        return (object)[
            'answer' => (object)[
                'balance' => (string)$user->balance,
                'bonus_balance' => "0",
                'user_id' => (string)$user->id,
                'operator_id' => $requestContent->data->operator_id,
                'currency' => $requestContent->data->currency,
                'user_nickname' => (string)$user->username,
                'auth_token' => $requestContent->data->user_auth_token,
                'game_token' => $game_token,
                'error_code' => 0,
                'error_description' => "ok",
                'timestamp' => (string)time(),
            ],
            'api' => $requestContent->api,
            'success' => true,
        ];
    }

    private function getFeatures($requestContent)
    {
        $user = User::find($requestContent->data->user_id);
        $promocodeActivation = PromocodeActivation::where('user_id', $user->id)
            ->where('status', 0)
            ->latest('created_at')
            ->first();

        $answer = [
            'balance' => (string)$user->balance,
            'bonus_balance' => 0,
            'user_id' => (string)$user->id,
            'operator_id' => $requestContent->data->operator_id,
            'currency' => $requestContent->data->currency,
            'game_token' => $user->remember_token,
            'user_nickname' => (string)$user->username,
            'error_code' => 0,
            'error_description' => "ok",
            'timestamp' => (string)time(),
        ];

        if ($promocodeActivation) {
            $promocode = Promocode::find($promocodeActivation->promo_id);
            if (isset($promocode->id_spin) && $user->current_id == $promocode->id_spin) {

                $gameSettings = $this->fetchGameSettings($promocode->id_spin);

                if ($promocode->id_spin >= 5000 && $promocode->id_spin <= 5029) {
                    $cp = 5;
                } else {
                    $cp = 5;
                }

                $answer['free_rounds'] = [
                    'id' => $promocode->id,
                    'count' => $promocode->quantity_spin,
                    'bet' => intval($promocode->sum),
                    'lines' => $gameSettings['data']['lines'][0] ?? 1,
                    'mpl' => 1,
                    'cp' => $cp,
                    'version' => 0,
                ];
            }
        }

        return (object)[
            'answer' => (object)$answer,
            'api' => $requestContent->api,
            'success' => true,
        ];
    }

    public function fetchGameSettings($slot_id)
    {
        $slot = B2bSlot::find($slot_id);

        $curl = curl_init();

        $headers = [
            'Content-Type: application/json'
        ];

        $url = 'https://int4new.datachannel.cloud/.control';

        $queryParams = json_encode([
            "api" => "plugin",
            "data" => [
                "name" => "info",
                "action" => "getGameSettings",
                "data" => [
                    "gameId" => $slot->gm_bk_id,
                    "currency" => "RUB",
                    "provider" => 40115,
                    "pass" => "0177b08aa9cd80b5995af21cd9c6759969f34823",
                ],
            ],
        ]);

        try {

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $queryParams,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            ]);

            $response = curl_exec($curl);

            return json_decode($response, true);
        } catch (\Exception $e) {
            Log::error('Ошибка при запросе к API', [
                'exception' => $e->getMessage(),
                'data' => $queryParams,
            ]);
        }
    }

    private function activateFeatures($requestContent)
    {
        $user = User::find($requestContent->data->user_id);

        return (object)[
            'answer' => (object)[
                'balance' => (string)$user->balance,
                'bonus_balance' => 0,
                'user_id' => (string)$user->id,
                'operator_id' => $requestContent->data->operator_id,
                'currency' => $requestContent->data->currency,
                'game_token' => $user->remember_token,
                'user_nickname' => (string)$user->username,
                'error_code' => 0,
                'error_description' => "ok",
                'timestamp' => (string)time()
            ],
            'api' => $requestContent->api,
            'success' => true,
        ];
    }

    private function updateFeatures($requestContent)
    {
        $user = User::find($requestContent->data->user_id);

        return (object)[
            'answer' => (object)[
                'balance' => (string)$user->balance,
                'bonus_balance' => 0,
                'user_id' => (string)$user->id,
                'operator_id' => $requestContent->data->operator_id,
                'currency' => $requestContent->data->currency,
                'game_token' => $user->remember_token,
                'user_nickname' => (string)$user->username,
                'error_code' => 0,
                'error_description' => "ok",
                'timestamp' => (string)time()
            ],
            'api' => $requestContent->api,
            'success' => true,
        ];
    }

    public function endFeatures($request)
    {
        try {
            $user = $this->getUser($request->data->user_id, $request->data->user_game_token, $request->data->currency);

            $promocodeActivation = PromocodeActivation::where('user_id', $user->id)
                ->where('status', 0)
                ->latest('created_at')
                ->first();

            if ($promocodeActivation) {
                $promocodeActivation->update(['status' => 1]);

                $promocode = Promocode::find($promocodeActivation->promo_id);
                Action::create([
                    'user_id' => $user->id,
                    'action' => 'Активация Промокода ФС (' . $promocode->name . ')',
                    'balanceBefore' => $user->balance,
                    'balanceAfter' => $user->balance + $promocode->sum
                ]);
            }

            $response = new \stdClass();
            $response->answer = new \stdClass();

            $response->answer->balance = "$user->balance";
            $response->answer->bonus_balance = 0;
            $response->answer->game_id = 0;
            $response->answer->user_id = "$user->id";
            $response->answer->operator_id = '40115';
            // $response->answer->currency = $user->current_currency;
            $response->answer->currency = 'RUB';
            $response->answer->game_token = $user->remember_token;
            $response->answer->user_nickname = "$user->username";
            $response->answer->error_code = 0;
            $response->answer->error_description = "ok";
            $response->answer->timestamp = '"' . time() . '"';
            $response->api = $request->api;
            $response->success = true;

            \Log::debug('[endFeatures] ' . json_encode($response));

            return $response;
        } catch (\Throwable $e) {
            \Log::error('[endFeatures] ' . $e->getMessage(), ['exception' => $e]);
            $response = new \stdClass();
            $response->success = false;
            $response->error = true;
            $response->message = 'Ошибка endFeatures: ' . $e->getMessage();
            return $response;
        }
    }

    public function getUser($user_id, $game_token, $currency)
    {
        $user = User::where('id', $user_id)->first();

        if (!$user) {
            throw new Exception("User not found");
        }

        return $user;
    }

    private function debit($requestContent)
    {
        $user = User::find($requestContent->data->user_id);
        if ($user->balance < $requestContent->data->debit_amount) {
            throw new \Exception("Insufficient funds");
        }

        $amount = $this->userDebitUpdateAmount($requestContent->data->user_id, $requestContent->data->debit_amount);


        return (object)[
            'answer' => (object)[
                'transaction_id' => $requestContent->data->transaction_id,
                'balance' => (string)$amount,
                'bonus_balance' => "0",
                'user_id' => (string)$user->id,
                'operator_id' => $requestContent->data->operator_id,
                'currency' => $requestContent->data->currency,
                'game_token' => $user->remember_token,
                'user_nickname' => (string)$user->username,
                'error_code' => 0,
                'error_description' => "ok",
                'timestamp' => (string)time(),
            ],
            'api' => $requestContent->api,
            'success' => true,
        ];
    }

    private function credit($requestContent)
    {
        $user = User::find($requestContent->data->user_id);

        $amount = $this->userCreditUpdateAmount($requestContent->data->user_id, $requestContent->data->credit_amount, $requestContent->data->currency, $requestContent->data->credit_type);

        return (object)[
            'answer' => (object)[
                'transaction_id' => $requestContent->data->transaction_id,
                'balance' => (string)$amount,
                'bonus_balance' => "0",
                'user_id' => (string)$user->id,
                'operator_id' => $requestContent->data->operator_id,
                'currency' => $requestContent->data->currency,
                'game_token' => $user->remember_token,
                'user_nickname' => (string)$user->username,
                'error_code' => 0,
                'error_description' => "ok",
                'timestamp' => (string)time(),
            ],
            'api' => $requestContent->api,
            'success' => true
        ];
    }

    public function providers()
    {
        try {
            $desiredProviders = [
                'Igrosoft',
                "YggDrasil",
                "Novomatic Deluxe",
                "Play'n GO",
                "Push Gaming"
            ];
            $providers = B2bSlot::query()
                ->where('show', 1)
                ->whereIn('gr_title', $desiredProviders)
                ->select('gr_title as title')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('gr_title')
                ->get();

            // Если нет провайдеров, возвращаем пустой массив
            if ($providers->isEmpty()) {
                return response()->json([]);
            }

            // Преобразуем в массив вручную для большей надежности
            $result = $providers->map(function ($provider) {
                // После select('gr_title as title') поле доступно только как title
                $title = $provider->title ?? 'Unknown';
                return [
                    'title' => $title,
                    'img' => $title . '.svg',
                    'count' => (int)($provider->count ?? 0)
                ];
            });
            
            return response()->json($result->values()->all());
        } catch (\Throwable $e) {
            \Log::error('Ошибка в B2B providers: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([]); // Возвращаем пустой массив вместо 500
        }
    }

    public function show($id)
    {
        $slot = B2bSlot::where('id', $id)->first();

        if (!$slot) {
            return response()->json(['error' => true, 'message' => 'Данный слот не найден'], 404);
        }

        return response()->json(['success' => true, 'slot' => $slot], 200);
    }

    public function info($id)
    {
        $slot = B2bSlot::find($id) ? ['source' => 'b2b', 'data' => B2bSlot::find($id)] : (MobuleSlot::find($id) ? ['source' => 'mobule', 'data' => MobuleSlot::find($id)] : null);

        if (!$slot) {
            return response()->json(['error' => true, 'message' => 'Данный слот не найден'], 404);
        }

        return response()->json(['success' => true, 'slot' => $slot], 200);
    }

    public function userCreditUpdateAmount($user_id, $credit_amount, $currency, $type)
    {
        try {
            $user = User::where('id', $user_id)->first();
    
            if ($credit_amount > 0) {
                if ($type != "freeRounds") {
                    Action::create([
                        'user_id' => $user->id,
                        'action' => 'slot (+' . $credit_amount . ')',
                        'balanceBefore' => $user->balance,
                        'balanceAfter' => $user->balance + $credit_amount
                    ]);
                } else {
                    $promocodeActivation = PromocodeActivation::where('user_id', $user->id)
                        ->latest('created_at')
                        ->first();
                    // $promocodeActivation->update(['status' => 1]);
                    $promocode = Promocode::find($promocodeActivation->promo_id);
                    // $user->increment('wager', $credit_amount * $promocode->wager);
                    $user->increment('slots_wager', $credit_amount * 3);
                    Action::create([
                        'user_id' => $user->id,
                        'action' => 'FS (+' . $credit_amount . ')',
                        'balanceBefore' => $user->balance,
                        'balanceAfter' => $user->balance + $credit_amount
                    ]);
                }
    
                $user->increment('slots', $credit_amount);
    
                $user->balance += $credit_amount;
                $user->save();
    
                $slot = B2bSlot::where('id', $user->current_id)->first();
    
                $coef = 0;
                if ($user->current_bet > 0) {
                    $coef = number_format(($credit_amount / $user->current_bet), 2);
                }
    
                if (!$user->is_youtuber && $type != "freeRounds" && $slot && $user && $slot->gr_title) {
                    $image = SlotServiceFacade::getImage($slot);
                    if ($image !== null) {
                        Redis::publish('slotsHistory', json_encode([
                            'id' => $slot->id,
                            'game_id' => $user->current_id,
                            'image' => $image,
                            'slot_name' => $slot->gr_title,
                            'username' => $user->username,
                            'coef' => $coef,
                            'win' => $credit_amount
                        ]));
                    }
                }
            }
    
            $amount = User::where('id', $user_id)->first();
    
            if (!$amount) {
                throw new \Exception("Feath amount error");
            }
    
            return $amount->balance;
        } catch (\Throwable $e) {
            \Log::error('userCreditUpdateAmount error', [
                'user_id' => $user_id,
                'credit_amount' => $credit_amount,
                'currency' => $currency,
                'type' => $type,
                'exception' => $e
            ]);
            return null; // или throw $e; если нужно пробросить ошибку дальше
        }
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
