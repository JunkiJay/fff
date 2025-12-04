<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Payment;
use App\Models\Slots\Slot;
use App\Models\User;
use App\Services\Slots\Facades\SlotServiceFacade;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Redis;

class SlotsController extends Controller
{
    public function init(Request $r)
    {
        switch ($r->provider) { // PIZDEC
            case 'list':
                $slots = Slot::where('show', 1)->orderBy('priority', 'desc')->orderBy('id', 'asc')->paginate(21);
                break;

            case 'netent':
                $slots = Slot::where('show', 1)->where('provider', 'netent')->orderBy('id', 'asc')->paginate(21);
                break;

            case 'Playn Go':
                $slots = Slot::where('show', 1)->where('provider', 'Playn Go')->orderBy('id', 'asc')->paginate(21);
                break;

            case 'Pragmatic Play':
                $slots = Slot::where('show', 1)->where('provider', 'Pragmatic Play')->orderBy('id', 'asc')->paginate(21);
                break;

            case 'YggDrasil':
                $slots = Slot::where('show', 1)->where('provider', 'YggDrasil')->orderBy('id', 'asc')->paginate(21);
                break;

            case 'Igrosoft':
                $slots = Slot::where('show', 1)->where('provider', 'Igrosoft')->orderBy('id', 'asc')->paginate(21);
                break;

            default:
                $slots = Slot::where('show', 1)->orderBy('priority', 'desc')->orderBy('id', 'asc')->paginate(18);
        }

        if (isset($r->search)) {
            $slots = Slot::where([['show', 1], ['title', 'LIKE', '%' . $r->search . '%']])->orderBy('priority', 'desc')->orderBy('id', 'asc')->paginate(16);
        }

        return $slots;
    }

    public function getSlotWithPagenate(Request $r)
    {
        $category = $r->provider;
        if ($category == 'all') {
            $category = '';
        }

        $search = $r->search;
        $query = Slot::query();
        if ($category) {
            $query->where('provider', $category);
        }

        if (strlen($search) > 0) {
            $query->where('title', 'LIKE', '%' . $search . '%');
        }

        $query->where('show', 1);

        $results = $query->orderBy('id', 'desc')->get();

        $results = $results->map(function ($slot) {
            $slot->icon = '/assets/image/slots/' . str_replace(" ", "", $slot->title) . '.jpg';
            return $slot;
        });

        $slots = $this->paginate($results, $r->count, $r->page);

        return $slots;
    }

    public function paginate($items, $perPage = 20, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function getRandom()
    {
        $slot = Slot::where([['show', 1]])->inRandomOrder()->get();

        return response()->json(['id' => $slot[0]->game_id]);
    }

    public function countSlots()
    {
        $providers = ['all', 'netent', 'Pragmatic Play', 'Playn Go', 'YggDrasil', 'Igrosoft', 'Novomatic'];
        $names = [
            'all' => 'Все игры',
            'netent' => 'NetEnt',
            'Pragmatic Play' => 'Pragmatic Play',
            'Playn Go' => 'Play’n GO',
            'YggDrasil' => 'YggDrasil',
            'Igrosoft' => 'Igrosoft',
            'Novomatic' => 'Novomatic',
        ];

        $count = [];

        foreach ($providers as $p) {
            if ($p == 'all') {
                $slots = Slot::where([['show', 1]])->count();
            } else {
                $slots = Slot::where([['provider', $p], ['show', 1]])->count();
            }

            $count[] = ['provider' => $p, 'name' => $names[$p], 'games' => $slots];
        }

        return $count;
    }

    public function loadSlot(Request $r)
    {
        // $user = User::find(1);
        Auth::user();
        if (Auth::guest())
            return [
                'error' => true,
                'message' => 'Авторизуйтесь'
            ];

        if (Auth::user()->ban) {
            return [
                'error' => true,
                'message' => 'Ваш аккаунт заблокирован'
            ];
        }

        $slot = Slot::where('show', 1)->where('game_id', $r->id)->first();
        if (!$slot) {
            return [
                'error' => true,
                'message' => 'Данный слот не найден'
            ];
        }

        $user = User::where('id', Auth::id())->first();
        $psum = Payment::where([['created_at', '>=', today()->subDays(7)], ['user_id', $user->id], ['status', 1]])->sum('sum');

        if ($user->auth_token == null) {
            $user->auth_token = bin2hex(random_bytes(20));
        }

        $user->current_id = $slot->game_id;
        $user->save();
        if (!$this->user->is_admin && $psum < 100 && !$this->user->is_youtuber) {
            $link = "https://int.apichannel.cloud/gamesbycode/$slot->game_id.gamecode?operator_id=40198&language=ru&user_id={$user->id}&auth_token={$user->auth_token}&currency=RUB&desktop_interface=1&home_url=".env('APP_URL')."/slots";
        } else {
            $link = "https://int.apichannel.cloud/gamesbycode/$slot->game_id.gamecode?operator_id=" . ($user->is_youtuber ? '40114' : '40198') . "&language=ru&user_id={$user->id}&auth_token={$user->auth_token}&currency=RUB&desktop_interface=1&home_url=".env('APP_URL')."/slots";
        }

        return response()->json(['title' => $slot->title, 'link' => $link]);
    }

    public function callback()
    {
        try {
            header("Connection: close");

            $data = file_get_contents('php://input');
            $request = json_decode($data);

            switch ($request->api) {
                case 'do-auth-user-ingame':
                    $data = $this->auth($request);
                    echo json_encode($data);
                    break;

                case 'do-debit-user-ingame':
                    $data = $this->debit($request);
                    echo json_encode($data);
                    break;

                case 'do-credit-user-ingame':
                    $data = $this->credit($request);
                    echo json_encode($data);
                    break;

                case 'do-rollback-user-ingame':
                    $data = $this->rollback($request);
                    echo json_encode($data);
                    break;

                case 'do-get-features-user-ingame':
                    $data = $this->getFeatures($request);
                    echo json_encode($data);
                    break;

                case 'do-activate-features-user-ingame':
                    $data = $this->activateFeatures($request);
                    echo json_encode($data);
                    break;

                case 'do-end-features-user-ingame':
                    $data = $this->endFeatures($request);
                    echo json_encode($data);
                    break;

                default:
                    throw new \Exception("Unknown api");
            }
        } catch (\Exception $e) {
            $response = new \stdClass();
            $response->answer = new \stdClass();
            $response->answer->error_code = 1;
            $response->answer->error_description = $e->getMessage();
            $response->answer->timestamp = '"' . time() . '"';
            $response->api = $request->api;
            $response->success = true;
            echo json_encode($response);
        }
    }

    public function auth($request)
    {
        $user = $this->getUserAuth($request->data->user_id, $request->data->user_auth_token, $request->data->currency);
        $game_token = md5(time() . mt_rand(1, 1000000));
        $this->initSession($request->data->user_id, $game_token, $request->data->currency);

        $response = new \stdClass();
        $response->answer = new \stdClass();
        $response->answer->balance = "{$user->balance}";
        $response->answer->bonus_balance = "0";
        $response->answer->user_id = "$user->id";
        $response->answer->operator_id = $user->is_youtuber ? '40114' : '40115';
        $response->answer->currency = $request->data->currency;
        $response->answer->user_nickname = "$user->username";
        $response->answer->auth_token = $request->data->user_auth_token;
        $response->answer->game_token = $game_token;
        $response->answer->error_code = 0;
        $response->answer->error_description = "ok";
        $response->answer->timestamp = '"' . time() . '"';
        $response->api = $request->api;
        $response->success = true;

        return $response;
    }

    public function debit($request)
    {
        $user = $this->getUser($request->data->user_id, $request->data->user_game_token, $request->data->currency);

        if ($user->balance < $request->data->debit_amount) {
            throw new \Exception("Not enought amount");
        }

        $amount = $this->userDebitUpdateAmount($request->data->user_id, $request->data->debit_amount, $request->data->currency);
        $this->userUpdateGameTokenDate($request->data->user_id);

        $response = new \stdClass();
        $response->answer = new \stdClass();
        $response->answer->transaction_id = $request->data->transaction_id;
        $response->answer->balance = "$amount";
        $response->answer->bonus_balance = "0";
        $response->answer->user_id = "$user->id";
        $response->answer->operator_id = $user->is_youtuber ? '40114' : '40115';
        $response->answer->currency = $user->current_currency;
        $response->answer->game_token = $user->game_token;
        $response->answer->user_nickname = "$user->username";
        $response->answer->error_code = 0;
        $response->answer->error_description = "ok";
        $response->answer->timestamp = '"' . time() . '"';
        $response->api = $request->api;
        $response->success = true;

        return $response;
    }

    public function userDebitUpdateAmount($user_id, $debit_amount, $currency)
    {
        $user = User::where('id', $user_id)->first();
        Action::create([
            'user_id' => $user->id,
            'action' => 'slots (-'.$debit_amount.')',
            'balanceBefore' => $user->balance,
            'balanceAfter' => $user->balance - $debit_amount
        ]);

        if (!(Cache::has('user.' . $user->id . '.historyBalance'))) {
            Cache::put('user.' . $user->id . '.historyBalance', '[]');
        }

        $hist_balance = array(
            'user_id' => $user->id,
            'type' => 'slots (-' . $debit_amount . ')',
            'balance_before' => $user->balance,
            'balance_after' => $user->balance - $debit_amount,
            'date' => date('d.m.Y H:i:s')
        );

        $cashe_hist_user = Cache::get('user.' . $user->id . '.historyBalance');
        $cashe_hist_user = json_decode($cashe_hist_user);
        $cashe_hist_user[] = $hist_balance;
        $cashe_hist_user = json_encode($cashe_hist_user);
        Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

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

    public function userCreditUpdateAmount($user_id, $credit_amount, $currency)
    {
        $user = User::where('id', $user_id)->first();

        if ($credit_amount > 0) {
            Action::create([
                'user_id' => $user->id,
                'action' => 'slots (+' . $credit_amount . ')',
                'balanceBefore' => $user->balance,
                'balanceAfter' => $user->balance + $credit_amount
            ]);

            if (!(Cache::has('user.' . $user->id . '.historyBalance'))) {
                Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'slots (+' . $credit_amount . ')',
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

            $slot = Slot::where('game_id', $user->current_id)->first();
            if (!$user->is_youtuber) {
                $url = SlotServiceFacade::getImage($slot);
                if ($url !== null) {
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
        }

        $user->balance += $credit_amount;
        $user->save();
        $user->refresh();

        return $user->balance;
    }

    public function userUpdateGameTokenDate($user_id)
    {
        User::where('id', $user_id)->update([
            'game_token_date' => date("Y-m-d H:i:s")
        ]);
    }

    public function credit($request)
    {
        $user = $this->getUser($request->data->user_id, $request->data->user_game_token, $request->data->currency);
        $amount = $this->userCreditUpdateAmount($request->data->user_id, $request->data->credit_amount, $request->data->currency);

        $response = new \stdClass();
        $response->answer = new \stdClass();
        $response->answer->transaction_id = $request->data->transaction_id;
        $response->answer->balance = "$amount";
        $response->answer->bonus_balance = "0";
        $response->answer->user_id = "$user->id";
        $response->answer->operator_id = $user->is_youtuber ? '40114' : '40115';
        $response->answer->currency = $user->current_currency;
        $response->answer->game_token = $user->game_token;
        $response->answer->user_nickname = "$user->username";
        $response->answer->error_code = 0;
        $response->answer->error_description = "ok";
        $response->answer->timestamp = '"' . time() . '"';
        $response->api = $request->api;
        $response->success = true;

        return $response;
    }

    public function getFeatures($request)
    {
        $user = $this->getUser($request->data->user_id, $request->data->user_game_token, $request->data->currency);

        $response = new \stdClass();
        $response->answer = new \stdClass();
        $response->answer->balance = "$user->balance";
        $response->answer->bonus_balance = 0;
        $response->answer->user_id = "$user->id";
        $response->answer->operator_id = $user->is_youtuber ? '40114' : '40115';
        $response->answer->currency = $user->current_currency;
        $response->answer->game_token = $user->game_token;
        $response->answer->user_nickname = "$user->username";
        $response->answer->error_code = 0;
        $response->answer->error_description = "ok";
        $response->answer->timestamp = '"' . time() . '"';
        $response->api = $request->api;
        $response->success = true;

        return $response;
    }

    public function activateFeatures($request)
    {
        $user = $this->getUser($request->data->user_id, $request->data->user_game_token, $request->data->currency);

        $response = new \stdClass();
        $response->answer = new \stdClass();
        $response->answer->balance = "$user->balance";
        $response->answer->bonus_balance = 0;
        $response->answer->game_id = 0;
        $response->answer->user_id = "$user->id";
        $response->answer->operator_id = $user->is_youtuber ? '40114' : '40115';
        $response->answer->currency = $user->current_currency;
        $response->answer->game_token = $user->game_token;
        $response->answer->user_nickname = "$user->username";
        $response->answer->error_code = 0;
        $response->answer->error_description = "ok";
        $response->answer->timestamp = '"' . time() . '"';
        $response->api = $request->api;
        $response->success = true;

        return $response;
    }

    public function endFeatures($request)
    {
        $user = $this->getUser($request->data->user_id, $request->data->user_game_token, $request->data->currency);

        $response = new \stdClass();
        $response->answer = new \stdClass();
        $response->answer->balance = "$user->balance";
        $response->answer->bonus_balance = 0;
        $response->answer->game_id = 0;
        $response->answer->user_id = "$user->id";
        $response->answer->operator_id = $user->is_youtuber ? '40114' : '40115';
        $response->answer->currency = $user->current_currency;
        $response->answer->game_token = $user->game_token;
        $response->answer->user_nickname = "$user->username";
        $response->answer->error_code = 0;
        $response->answer->error_description = "ok";
        $response->answer->timestamp = '"' . time() . '"';
        $response->api = $request->api;
        $response->success = true;

        return $response;
    }

    public function initSession($user_id, $game_token, $currency)
    {
        User::where('id', $user_id)->update([
            'game_token' => $game_token,
            'game_token_date' => date("Y-m-d H:i:s"),
            'current_currency' => $currency
        ]);

    }

    public function getUserAuth($user_id, $auth_token, $currency)
    {
        $user = User::where('id', $user_id)->first();

        if (!$user) {
            throw new \Exception("User not found");
        }

        if ($user->auth_token !== $auth_token) {
            throw new \Exception("auth_token not valid");
        }

        return $user;
    }

    public function getUser($user_id, $game_token, $currency)
    {
        $user = User::where('id', $user_id)->first();

        if (!$user) {
            throw new \Exception("User not found");
        }

        if ($user->game_token !== $game_token) {
            throw new \Exception("game_token not valid");
        }

        return $user;
    }
}