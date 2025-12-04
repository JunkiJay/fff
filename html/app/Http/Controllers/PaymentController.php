<?php

namespace App\Http\Controllers;

use App\Enums\Payments\PaymentStatusEnum;
use App\Models\Action;
use App\Models\Payment;
use App\Models\Promocode;
use App\Models\PromocodeActivation;
use App\Models\ReferralProfit;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Notifications\Facades\NotificationsServiceFacade;
use App\Services\Payments\Actions\Payments\PayAction;
use App\Services\Payments\DTO\CreatePaymentDTO;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\PaymentProvidersEnum;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function toWin(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return ['toWin' => 0];
        }

        $allCryptobotSum = Payment::where('system', 'cryptobot')
            ->where('status', 1)
            ->sum('sum') ?? 0;

        if ($allCryptobotSum == 0) {
            return ['toWin' => 0];
        }

        $allCryptobotSumUser = Payment::where('system', 'cryptobot')
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->sum('sum') ?? 0;

        $toWin = ($allCryptobotSumUser > 0) ? ($allCryptobotSumUser / $allCryptobotSum) * 100 : 0;

        return ['toWin' => $toWin];
    }

    public function create(Request $request)
    {
        try {
            $depositResult = PayAction::run(
                new CreatePaymentDTO(
                    $request->amount,
                    PaymentProvidersEnum::tryFrom($request->provider),
                    PaymentMethodEnum::tryFrom($request->get('method')),
                    auth()->user()
                )
            );

            if ($depositResult instanceof PaymentRedirectResult) {
                return [
                    'url' => $depositResult->url
                ];
            } elseif ($depositResult instanceof PaymentShowSBPResult) {
                return [
                    'success' => true,
                    'payment' => [
                        'receiver' => $depositResult->phone,
                        'name' => $depositResult->name,
                        'bank' => $depositResult->bank,
                        'amount' => $depositResult->amount
                    ]
                ];
            } elseif ($depositResult instanceof PaymentErrorResult) {
                return [
                    'error' => true,
                    'message' => $depositResult->error,
                ];
            }

        } catch (ValidationException $exception) {
            return [
                'error' => true,
                'message' => \Arr::first(\Arr::first($exception->errors()))
            ];
        } catch (\DomainException $exception) {
            return [
                'error' => true,
                'message' => $exception->getMessage()
            ];
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), ['exception' => $exception]);

            return [
                'error' => true,
                'message' => 'Выберите способ оплаты'
            ];
        }
    }

    public function handleExpay(Request $request)
    {
        // $allowed_ips = [
        //     '165.227.159.246',
        //     '157.245.17.198',
        //     '68.183.213.224',
        //     '109.198.124.21'
        // ];

        // $current_ip = $_SERVER['REMOTE_ADDR'];

        // if (!in_array($current_ip, $allowed_ips)) {
        //     die("Нельзя так делать, пошел нахуй");
        // }

        $requestData = $request->all();
        $trackerId = $requestData['tracker_id'];

        $requestData = array(
            'tracker_id' => $trackerId
        );

        $requestDataJson = json_encode($requestData);

        $timestamp = time();
        $privateKey = 's9kax24d11iao5md5hmt5kx73m32lsfzol88pyz1uh9q7zi99cq0nv0fuujsgrz79am5cbte5h23xcx7b1jinmn9mixyr6rvflm3bl4ik2i9cdhvvjlyqg5rpr99fg8c';
        $message = $timestamp . $requestDataJson;
        $signature = hash_hmac('sha512', $message, $privateKey);

        $headers = array(
            'Accept: application/json',
            'ApiPublic: mqbkjmrfgbx9dz05kdhx7g1v28n5doqbee7lpdfaco1v537kfbmwjyo7n91hxidl', // Вставь свой публичный ключ
            'Content-Type: application/json',
            'Signature: ' . $signature,
            'Timestamp: ' . $timestamp
        );

        // Инициализируем запрос
        $ch = curl_init();

        // Устанавливаем параметры запроса
        curl_setopt($ch, CURLOPT_URL, 'https://apiv2.expay.cash/api/transaction/get');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestDataJson);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Выполняем запрос и получаем ответ
        $response = curl_exec($ch);

        // Закрываем соединение
        curl_close($ch);

        $responseArray = json_decode($response, true);
        if ($responseArray && isset($responseArray['status']) && $responseArray['status'] === 'ok') {
            $status = $responseArray['transaction']['status'];
            $amount = $responseArray['transaction']['amount'];
            $clientTransactionId = $responseArray['transaction']['client_transaction_id'];
            $text = "Status: $status\nAmount: $amount\nClient Transaction ID: $clientTransactionId";
            $payment = Payment::find($clientTransactionId);
            if (!$payment || $payment->status == 1) {
                response()->json(['success' => 'false', 'msg' => 'Order not found!']);
            }

            if (intval($payment->sum) !== intval($amount)) {
                $payment->sum = $amount;
                $payment->save();
            }

            if (!$status || $status !== 'SUCCESS') {
                $payment->status = 2;
                $payment->save();
                return response()->json(['success' => 'false', 'msg' => 'Order not payed!']);
            }

            $incrementSum = $payment->sum;

            $user = User::find($payment->user_id);

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            if ($user->balance < 10) {
                $user->wager = 0;
                $user->save();
            }

            $user->increment('balance', $incrementSum);
            $user->increment('wager', $incrementSum * 3);

            Action::create([
                'user_id' => $user->id,
                'action' => 'Пополнение через Expay',
                'balanceBefore' => $user->balance - $incrementSum,
                'balanceAfter' => round($user->balance, 2)
            ]);

            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $payment->status = 1;
            $payment->save();

            return response()->json(['success' => 'true']);
        }
    }

    public function createPayou(Request $request)
    {
        $user = $request->user();
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }
        if ($request->system == 'MoneyRUB_Eig_Tips') {
            if ($request->amount < 100) {
                return [
                    'error' => true,
                    'message' => 'Минимальная сумма пополнения для выбранного метода 100р.'
                ];
            }
        } else {
            if ($request->amount < 500) {
                return [
                    'error' => true,
                    'message' => 'Минимальная сумма пополнения для выбранного метода 500р.'
                ];
            }
        }

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        // Настройки платежа
        $merchantId = '422'; // ID магазина
        $secretKey = '573403570e894b1f68ff23e0d14e2636'; // Секретное слово
        if ($request->system == 'MoneyRUB_Eig_Tips') {
            $sistems = 'MoneyRUB_Eig_Tips';
        } else {
            $sistems = 'MoneyRUB_Gt_qr';
        }

        $amount = strval(floatval($request->input('amount')));

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => $amount,
            "system" => $request->provider,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        $orderId = strval($order->id); // Уникальный ID заказа
        $comment = 'Test payment #' . $orderId; // Комментарий
        $userCode = strval($user->id); // ID пользователя
        $userEmail = $user->id . '@gmail.com'; // Email пользователя

        // Формирование подписи
        $hash = md5($merchantId . ":" . $amount . ":" . $secretKey . ":" . $sistems . ":" . $orderId);


        // Генерация формы
        $form = view(
            'payment.payou_form',
            compact(
                'merchantId',
                'sistems',
                'amount',
                'orderId',
                'comment',
                'userCode',
                'userEmail',
                'hash'
            )
        )->render();


        return response()->json(['form' => $form, 'window_open' => true]);
    }

    public function callbackPayou(Request $request)
    {
        \Log::debug($request);

        $id = intval($request['MERCHANT_ORDER_ID']);
        $payment = Payment::where('id', $id)->first();
        $amount = $request['AMOUNT'];

        if (!$payment) {
            return ['error' => 'Order not found'];
        }

        $user = User::where('id', $payment->user_id)->first();

        if ($amount > 0 && $payment->status != 1) {
            if ($amount != $payment->sum) {
                $payment->sum = $amount;
                $payment->save();
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Пополнение через Payou',
                'balance_before' => $user->balance - $incrementSum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }

    public function create1plat(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return [
                'error' => true,
                'message' => 'Ваш аккаунт заблокирован'
            ];
        }
        if ($user->limit_payment) {
            return [
                'error' => true,
                'message' => 'Платежи ограничены'
            ];
        }

        $amount = intval($request->amount);

        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 100р.'
            ];
        }

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => $amount,
            "system" => $request->provider,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        // Данные для аутентификации
        $shopId = config('api-clients.1plat.shop_id');
        $secret = config('api-clients.1plat.secret');

        // Получаем данные из запроса
        $merchantOrderId = $order->id;
        $userId = $user->id;
        $email = $userId . "@sweetx1.pro";

        // URL API
        $apiUrl = config('api-clients.1plat.base_url') . 'merchant/order/create/by-api';

        // Подготовка тела запроса
        $payload = [
            'merchant_order_id' => $merchantOrderId,
            'user_id' => $userId,
            'amount' => $amount,
            'email' => $email,
            'method' => 'sbp'
        ];

        // Инициализация cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "x-shop: {$shopId}",
                "x-secret: {$secret}",
                "Content-Type: application/json",
            ],
        ]);

        // Выполнение запроса
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // Проверка на ошибку
        if ($err) {
            return response()->json(['error' => 'cURL Error: ' . $err], 500);
        }

        // Преобразование ответа в массив
        $responseData = json_decode($response, true);

        // return ['error' => true, 'message' => $responseData];

        \Log::debug($responseData);

        // Возврат результата
        if (!empty($responseData['success']) && $responseData['success'] === 1) {
            return response()->json([
                'success' => true,
                'url' => $responseData['url'], // Ссылка на форму оплаты
                'payment' => $responseData['payment'],
            ]);
        }

        return [
            'error' => true,
            'message' => 'Подождите 1 минуту прежде чем создавать новую заявку! или поменяйте сумму пополнения!'
        ];
        // 'Попробуйте попытку через 15 минут или используйте другой метод'
    }

    public function callback1plat(Request $request)
    {
        if (!in_array($this->getIp(), ['159.89.25.81', '165.22.81.141'])) {
            \Log::debug('wrong ip', [$this->getIp()]);
            return 'wrong ip';
        }

        \Log::info('1plat callback', $request->toArray());

        $id = intval($request['merchant_id']);
        $payment = Payment::where('id', $id)->first();
        $amount = $request['amount'];

        if (!$payment) {
            return ['error' => 'Order not found'];
        }

        $user = User::where('id', $payment->user_id)->first();

        if ($amount > 0 && $payment->status != 1) {
            if ($amount != $payment->sum) {
                $payment->sum = $amount;
                $payment->save();
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            Action::create([
                'user_id' => $user->id,
                'action' => 'Пополнение через 1plat',
                'balanceBefore' => $user->balance - $incrementSum,
                'balanceAfter' => round($user->balance, 2)
            ]);

            NotificationsServiceFacade::sendDepositConfirmation($payment);

            $payment->status = PaymentStatusEnum::SUCCESS;
            $payment->save();
        }
        return ['ok'];
    }

    public function createRoyalFinance(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = floatval($request->amount);

        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 100р.'
            ];
        }

        // if ($amount > 999) {
        //     return [
        //         'error' => true,
        //         'message' => 'Максимальная сумма пополнения для выбранного метода 999р.'
        //     ];
        // }
        $type = $request->rf_type;


        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => $amount,
            "system" => $request->provider,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        if ($request->system == 'royalfinance_card') {
            $type = 'to_card_number';
        } elseif ($request->system == 'royalfinance_sbp') {
            $type = 'to_sbp_number';
        } else {
            $type = 'to_yandex_tips';
        }

        // Параметры запроса для Royal Finance
        $params = [
            'outter_id' => strval($order->id),
            // 'redirect_url' => 'https://stimule1.win',
            // 'success_redirect_url' => 'https://stimule1.win',
            // 'fail_redirect_url' => 'https://stimule1.win/pay',
            'sum' => $amount,
            'type' => $type
        ];

        // Ваш токен для авторизации
        $token = '9d864eb2eeea7ebc3f6ff3d2a3684a1a56aa4604'; // Замените на ваш реальный токен 6858ef8ba2bb56e333731a9a2fd290c1d53a3e7c 

        try {
            // Создаем cURL запрос
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://royal-finance.org/api/v2/payments/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Token ' . $token,
                    'Content-Type: application/json',
                ],
            ]);

            // Выполняем запрос
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($error) {
                // Логируем ошибку cURL
                \Log::debug('Error', ['error' => $error]);

                return response()->json(['error' => 'Ошибка соединения с API Royal Finance'], 500);
            }

            // Преобразуем ответ в массив
            $data = json_decode($response, true);

            return ['error' => true, 'message' => $data];

            \Log::debug('Royal Finance API response',  $data);

            // Проверяем успешность запроса
            if (isset($data['nspk_url'])) {
                $order->merchant_meta = $data['id'];
                $order->save();

                return response()->json(["url" => $data["nspk_url"]], 200);
            } else {
                // Обработка ошибок API
                \Log::debug(['Royal Finance API error response: ' => $data]);

                return response()->json(['error' => 'Ошибка при обработке платежа'], 400);
            }
        } catch (\Exception $e) {
            // Логируем исключение
            \Log::debug(['Ошибка при выполнении запроса: ' => $e->getMessage()]);

            return response()->json(['error' => 'Не удалось выполнить запрос'], 500);
        }
    }

    public function createRoyalFinanceNspk(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = floatval($request->amount);

        if ($amount < 2010) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 2010.'
            ];
        }


        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => $amount,
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        // Параметры запроса для Royal Finance
        $params = [
            'outter_id' => strval($order->id),
            // 'redirect_url' => 'https://stimule1.win',
            // 'success_redirect_url' => 'https://stimule1.win',
            // 'fail_redirect_url' => 'https://stimule1.win/pay',
            'sum' => $amount,
            'type' => 'nspk'
        ];

        // Ваш токен для авторизации
        $token = 'a166aabd7417f03dd910e3b8f1bbf24a4a4ef1e4'; // Замените на ваш реальный токен 6858ef8ba2bb56e333731a9a2fd290c1d53a3e7c 

        try {
            // Создаем cURL запрос
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://royal-finance.org/api/v2/payments/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Token ' . $token,
                    'Content-Type: application/json',
                ],
            ]);

            // Выполняем запрос
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($error) {
                // Логируем ошибку cURL
                \Log::debug(['error' => $error]);

                return response()->json(['error' => 'Ошибка соединения с API Royal Finance'], 500);
            }

            // Преобразуем ответ в массив
            $data = json_decode($response, true);

            \Log::debug(['Royal Finance API response: ' => $data]);

            // Проверяем успешность запроса
            if (isset($data['nspk_url'])) {
                $order->merchant_meta = $data['id'];
                $order->save();

                return response()->json(["url" => $data["nspk_url"]], 200);
            } else {
                // Обработка ошибок API
                \Log::debug(['Royal Finance API error response: ' => $data]);

                return ['error' => true, 'message' => $data['error']];
            }
        } catch (\Exception $e) {
            // Логируем исключение
            \Log::debug(['Ошибка при выполнении запроса: ' => $e->getMessage()]);

            return response()->json(['error' => 'Не удалось выполнить запрос'], 500);
        }
    }

    public function royalfinanceTransgran(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = floatval($request->amount);

        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 100.'
            ];
        }


        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => $amount,
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        // Параметры запроса для Royal Finance


        $params = [
            'sum' => $amount,
            'type' => 'to_transgran_sbp',
            'client_id' => strval($order->id),
            'client_ip' => $request->ip(),
            'geo' => 'Россия'
        ];


        // Ваш токен для авторизации
        $token = '87494c21e28976e15a10d1f6138b25d35153d87c'; // Замените на ваш реальный токен 6858ef8ba2bb56e333731a9a2fd290c1d53a3e7c 

        try {
            // Создаем cURL запрос
            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://royal-finance.org/api/v2/payments/', // https://api.royalpay.cc/api/v1/payments/
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Token ' . $token,
                    'Content-Type: application/json',
                ],
            ]);

            // Выполняем запрос
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($error) {
                // Логируем ошибку cURL
                \Log::debug(['error' => $error]);

                return response()->json(['error' => 'Ошибка соединения с API Royal Finance'], 500);
            }

            // Преобразуем ответ в массив
            $data = json_decode($response, true);

            // return ['error' => true, 'message' => $data];

            \Log::debug(['Royal Finance API response: ' => $data]);

            // Проверяем успешность запроса
            if (isset($data['tpay_link'])) {
                $order->merchant_meta = $data['id'];
                $order->save();

                return response()->json(["url" => $data["tpay_link"]], 200);
            } else {
                // Обработка ошибок API
                \Log::debug(['Royal Finance API error response: ' => $data]);

                return ['error' => true, 'message' => $data['error']];
            }
        } catch (\Exception $e) {
            // Логируем исключение
            \Log::debug(['Ошибка при выполнении запроса: ' => $e->getMessage()]);

            return response()->json(['error' => 'Не удалось выполнить запрос'], 500);
        }
    }


    public function callbackRoyalFinance(Request $request)
    {
        // Получаем список IP-адресов через $this->getIp()
        $ipList = explode(',', $this->getIp());

        // Убираем пробелы вокруг каждого IP
        $ipList = array_map('trim', $ipList);

        // Ваш целевой IP, который нужно найти
        $allowedIp = '134.209.192.8';

        // Проверяем, есть ли нужный IP в списке
        if (!in_array($allowedIp, $ipList)) {
            \Log::debug(['wrong ip' => $this->getIp()]);
            return 'wrong ip';
        }

        \Log::debug($request);
        $id = intval($request['outter_id']);
        $payment = Payment::where('id', $id)->first();
        $amount = $request['amount'];
        if (!$payment) {
            return ['error' => 'Order not found'];
        }
        if ($request['status'] == 'declined') {
            $payment->status = 2;
            $payment->save();
            return ['ok'];
        }
        $user = User::where('id', $payment->user_id)->first();

        if ($amount > 0 && $payment->status != 1 && $request['status'] == 'completed') {
            if ($amount != $payment->sum) {
                $payment->sum = $amount;
                $payment->save();
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Пополнение через Royal Finance',
                'balance_before' => $user->balance - $incrementSum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }

    public function createNirvana(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = floatval($request->amount);

        if ($request->system == 'nirvana_nspk') {
            if ($amount < 500) {
                return [
                    'error' => true,
                    'message' => 'Минимальная сумма пополнения для выбранного метода 500р.'
                ];
            }
        } else {
            if ($amount < 1000) {
                return [
                    'error' => true,
                    'message' => 'Минимальная сумма пополнения для выбранного метода 1000р.'
                ];
            }
        }


        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => $amount,
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        $secret = 'c19284e5-bfc3-49fc-8773-6e05be0859c1';
        $public = '46e6eb02-8c1d-48e3-a3c9-04424d63ab89';

        if ($request->system == 'nirvana_nspk') {
            $paymentCode = 'NSPK';
        } else {
            $paymentCode = 'SBPRUB';
        }


        $params = [
            "paymentCode" => $paymentCode,
            "amount" => (int)$amount,
            "redirectURL" => config('app.url'),
            "header" => 'Stimule',
            "callbackURL" => config('app.url') . '/callback/nirvana?externalID=' . $order->id,
            "externalID" => (string)$order->id,
            "currency" => 'RUB'
        ];

        $jsonData = json_encode($params);
        $sign = hash_hmac('sha512', $jsonData, $secret);


        try {
            $apiUrl = "https://f.nirvanapay.pro/api/pay_in";

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'ApiPublic: ' . $public,
                'Signature: ' . $sign,
            ]);


            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headerString = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            curl_close($ch);

            $warning = $this->getHeaderValue($headerString, 'Warning');

            // Преобразуем ответ в массив
            $data = json_decode($body, true);

            // return ['error' => true, 'message' => $response];

            \Log::debug(['Nirvana API response: ' => $data]);

            // Проверяем успешность запроса
            if (isset($data['redirectURL'])) {
                $order->merchant_meta = $order->id;
                $order->save();

                return response()->json(["url" => $data["redirectURL"]], 200);
            } else {
                // Обработка ошибок API
                \Log::debug(['Royal Finance API error response: ' => $data]);

                return ['error' => true, 'message' => $data['error']];
            }
        } catch (\Exception $e) {
            // Логируем исключение
            \Log::debug(['Ошибка при выполнении запроса: ' => $e->getMessage()]);

            return response()->json(['error' => 'Не удалось выполнить запрос'], 500);
        }
    }

    private function getHeaderValue($headerString, $headerName)
    {
        $headers = explode("\r\n", $headerString);
        foreach ($headers as $header) {
            if (stripos($header, $headerName . ':') === 0) {
                return trim(substr($header, strlen($headerName) + 1));
            }
        }
        return null;
    }

    public function callbackNirvana(Request $request)
    {
        \Log::debug($request->all());
        \Log::debug('Nirvana callback ' . $request['externalID']);
        $id = intval($request['externalID']);

        $secret = 'c19284e5-bfc3-49fc-8773-6e05be0859c1';
        $public = '46e6eb02-8c1d-48e3-a3c9-04424d63ab89';


        $params = [
            "externalID" => (string)$id
        ];

        $jsonData = json_encode($params);
        $sign = hash_hmac('sha512', $jsonData, $secret);

        try {
            $apiUrl = "https://f.nirvanapay.pro/api/order/status";

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'ApiPublic: ' . $public,
                'Signature: ' . $sign,
            ]);


            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headerString = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            curl_close($ch);

            $warning = $this->getHeaderValue($headerString, 'Warning');

            // Преобразуем ответ в массив
            $data = json_decode($body, true);

            // return $data;

            \Log::debug(['Nirvana API response: ' => $data]);


            $payment = Payment::where('id', $id)->first();
            $amount = $data['amount'];
            if (!$payment) {
                return ['error' => 'Order not found'];
            }
            if ($data['status'] == 'ERROR') {
                $payment->status = 2;
                $payment->save();
                return ['ok'];
            }
            $user = User::where('id', $payment->user_id)->first();

            if ($amount > 0 && $payment->status != 1 && $data['status'] == 'SUCCESS') {
                if ($amount != $payment->sum) {
                    $payment->sum = $amount;
                    $payment->save();
                }
                if (!is_null($user->referral_use)) {
                    $this->setReferralProfit($user->id, $payment->sum);
                }

                $incrementSum = $payment->bonus != 0
                    ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                    : $payment->sum;

                $user->increment('wager', $payment->sum * 3);
                $user->increment('balance', $incrementSum);

                if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                    \Cache::put('user.' . $user->id . '.historyBalance', '[]');
                }

                $hist_balance = array(
                    'user_id' => $user->id,
                    'type' => 'Пополнение через Nirvana',
                    'balance_before' => $user->balance - $incrementSum,
                    'balance_after' => round($user->balance, 2),
                    'date' => date('d.m.Y H:i:s')
                );

                $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

                $cashe_hist_user = json_decode($cashe_hist_user);
                $cashe_hist_user[] = $hist_balance;
                $cashe_hist_user = json_encode($cashe_hist_user);
                \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

                $payment->status = 1;
                $payment->save();
            }
            return ['ok'];
        } catch (\Exception $e) {
            // Логируем исключение
            \Log::debug(['Ошибка при выполнении запроса: ' => $e->getMessage()]);

            return response()->json(['error' => 'Не удалось выполнить запрос'], 500);
        }
    }


    public function createTransgran(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = floatval($request->amount);

        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения на данном направлении: 100 руб.'
            ];
        }
        $totalAmount = intval($amount * 100);

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => $amount,
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        $params = [
            "product" => "Оплата счета #" . $order->id,
            "amount" => $totalAmount,
            "currency" => "RUB",
            "redirectSuccessUrl" => "https://stimule1.win",
            "redirectFailUrl" => "https://stimule1.win",
            "callbackUrl" =>
                "https://stimule1.win/callback/spinpay33234278",
            "orderNumber" => strval($order->id),
            "customer" => [
                "email" => "user" . $user->id . "@gmail.com",
                "ip" => $this->getIp(),
            ],
            "locale" => "ru",
            "bank_account" => [
                "requisite_type" => "sbp"
            ],
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://business.cixsdpxj.info/api/v1/payments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer adfb35b0609a7b9b3a37",
                "content-type: application/json",
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return response()->json(["error" => "cURL Error: " . $err], 500);
        }

        $data = json_decode($response, true);

        if (!empty($data["selectorUrl"])) {
            $order->merchant_meta = $data['token'];
            return response()->json(["url" => $data["selectorUrl"]], 200);
        } else {
            return [
                'error' => true,
                'message' => $data
            ];
        }
    }

    /**
     * Корректирует сумму согласно новым и старым правилам.
     *
     * @param int|float $amount
     * @return int
     */
    private function adjustAmount($amount): int
    {
        $amount = (int)round($amount);

        // Список запрещённых сумм (блокируемых банком)
        $blockedAmounts = [
            100,
            110,
            111,
            123,
            132,
            150,
            180,
            200,
            213,
            231,
            250,
            300,
            312,
            321,
            333,
            350,
            400,
            401,
            444,
            450,
            500,
            501,
            550,
            555,
            600,
            601,
            650,
            666,
            700,
            701,
            750,
            777,
            800,
            801,
            850,
            888,
            900,
            901,
            950,
            999,
            1000,
            1001,
            1050,
            1111,
            2222,
            2501,
            3333,
            4444,
            5555,
            6666,
            7777,
            8888,
            9999,
        ];

        // Если сумма запрещена — применяем старые правила
        if (in_array($amount, $blockedAmounts, true)) {
            // Старые правила
            if ($amount < 100) {
                return 100;
            }
            if ($amount === 100) {
                return 110;
            }
            while (true) {
                if ($amount % 10 !== 0 || $amount % 100 === 0) {
                    $amount--;
                    if ($amount < 100) {
                        return 100;
                    }
                    continue;
                }
                break;
            }
            return $amount;
        }

        // Если сумма не запрещена — возвращаем как есть
        return $amount;
    }

    public function createSpinPay(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }
        $successful_orders = Payment::where("user_id", $user->id)
            ->where("status", 1)
            ->get();

        $last_orders = Payment::where("user_id", $user->id)
            ->where("system", "spinpay")
            ->whereDate("created_at", Carbon::today())
            ->orderBy("id", "desc")
            ->get();

        if (count($successful_orders) < 3) {
            return [
                'error' => true,
                'message' => 'Воспользуйтесь другим методом пополнения, лимит 3 заявки в день!'
            ];
        }

        $amount = $this->adjustAmount(floatval($request->amount));

        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения на данном направлении: 100 руб.'
            ];
        }
        $totalAmount = intval($amount * 100);

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => $amount,
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        $params = [
            "product" => "Оплата счета #" . $order->id,
            "amount" => $totalAmount,
            "currency" => "RUB",
            "redirectSuccessUrl" => config('app.url'),
            "redirectFailUrl" => config('app.url'),
            "callbackUrl" => config('app.url') . "/callback/spinpay68fsdd2",
            "orderNumber" => strval($order->id),
            "customer" => [
                "email" => "user" . $user->id . "@gmail.com",
                "ip" => $this->getIp(),
            ],
            "locale" => "ru",
            "bank_account" => [
                "requisite_type" => "link"
            ],
        ];

        $curl = curl_init();
        $baseUrl = config('api-clients.spinpay.base_url', 'https://business.cixsdpxj.info/api/v1/');
        $token = config('api-clients.spinpay.token', 'b99b5e53ecac311b748f2fc8c4cfacfdc631');

        curl_setopt_array($curl, [
            CURLOPT_URL => $baseUrl . "payments",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer " . $token,
                "content-type: application/json",
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        \Log::info('SpinPay API Request', [
            'url' => $baseUrl . "payments",
            'params' => $params,
            'token' => substr($token, 0, 10) . '...',
        ]);

        if ($err) {
            \Log::error('SpinPay cURL Error', ['error' => $err]);
            return response()->json(["error" => "cURL Error: " . $err], 500);
        }

        \Log::info('SpinPay API Response', [
            'http_code' => $httpCode,
            'raw_response' => $response,
        ]);

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('SpinPay JSON decode error', [
                'json_error' => json_last_error_msg(),
                'raw_response' => $response,
            ]);
            return response()->json(["error" => "Неверный формат ответа от API"], 500);
        }

        \Log::info('SpinPay parsed response', ['data' => $data]);

        if (isset($data["success"]) && $data["success"] && !empty($data["processingUrl"][0]["trader"])) {
            $traderUrl = $data["processingUrl"][0]["trader"];

            // Выполняем GET-запрос к трейдеру
            $curlTrader = curl_init();

            curl_setopt_array($curlTrader, [
                CURLOPT_URL => $traderUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_FOLLOWLOCATION => true,  // Следовать за редиректами
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "content-type: application/json", // Можно убрать, если сервер не требует
                ],
            ]);

            $traderResponse = curl_exec($curlTrader);
            $traderErr = curl_error($curlTrader);
            $httpCode = curl_getinfo($curlTrader, CURLINFO_HTTP_CODE); // Получаем статус код

            curl_close($curlTrader);


            // Логируем возможные ошибки
            if ($traderErr) {
                \Log::error("Trader cURL Error: " . $traderErr);
                return response()->json(["error" => "Trader cURL Error: " . $traderErr], 500);
            }

            // Проверяем статусный код, например, 200 OK
            if ($httpCode !== 200) {
                return response()->json(["error" => "Unexpected response from trader. HTTP Code: $httpCode"], 500);
            }

            // Декодируем JSON ответ
            $traderData = json_decode($traderResponse, true);


            // Проверяем наличие link->url в ответе трейдера
            if (!empty($traderData["link"]["url"])) {
                $order->merchant_meta = $traderData['token'];
                return response()->json(["url" => $traderData["link"]["url"]], 200);
            } else {
                return response()->json(["error" => "Не удалось получить ссылку из ответа Trader"], 400);
            }
        } else {
            \Log::error('SpinPay payment creation failed', [
                'response' => $data,
                'http_code' => $httpCode,
            ]);
            
            $errorMessage = $data["error_message"] ?? $data["message"] ?? $data["error"] ?? "Не удалось создать платеж";
            return response()->json(["error" => $errorMessage], 400);
        }
    }

    public function callbackSpinPay(Request $request)
    {
        if (!in_array($this->getIp(), ['35.159.146.42'])) {
            \Log::debug(['wrong ip' => $this->getIp()]);
            return 'wrong ip';
        }
        \Log::debug($request);
        $id = $request['orderNumber'];
        $payment = Payment::where('id', $id)->first();
        $amount = $request['amount'] / 100;
        if (!$payment) {
            return ['error' => 'Order not found'];
        }
        if ($request['status'] == 'declined') {
            $payment->status = 2;
            $payment->save();
            return ['ok'];
        }
        $user = User::where('id', $payment->user_id)->first();

        if ($amount > 0 && $payment->status != 1 && $request['status'] == 'approved') {
            if ($amount != $payment->sum) {
                $payment->sum = $amount;
                $payment->merchant_meta = $request['token'];
                $payment->save();
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            Action::create([
                'user_id' => $user->id,
                'action' => 'Пополнение через SpinPay(qr)',
                'balanceBefore' => $user->balance - $incrementSum,
                'balanceAfter' => round($user->balance, 2)
            ]);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }

    // public function handeHuyKonya(Request $request)
    // {
    //     if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //         die("wrong request method");
    //     }

    //     // Проверка на IP адрес сервиса (по желанию)
    //     // $ctx = stream_context_create([
    //     //     'http' => [
    //     //         'timeout' => 10
    //     //     ]
    //     // ]);

    //     // $allowed_ips = [
    //     //     '164.92.129.27'
    //     // ];

    //     // $current_ip = $_SERVER['REMOTE_ADDR'];

    //     // if (!in_array($current_ip, $allowed_ips)) {
    //     //     die("WRONG IP");
    //     // }

    //     $postData = $request->all();


    //     // Проверяем, если входные данные не являются валидными JSON
    //     if ($postData === null && json_last_error() !== JSON_ERROR_NONE) {
    //         die("Invalid JSON data");
    //     }

    //     // Проверяем, если входные данные содержат необходимые поля
    //     if (!isset($postData['transaction']['invoiceId']) || !isset($postData['status']) || !isset($postData['transaction']['pricing']['local']['amount'])) {
    //         die("Invalid input data format");
    //     }

    //     $invoiceId = $postData['transaction']['invoiceId'];
    //     $status = $postData['status'];
    //     $amount = $postData['transaction']['pricing']['local']['amount'];

    //     if ($status !== 'CONFIRMED') {
    //         die("Payment status is not CONFIRMED");
    //     }
    //     // Найдите платеж по invoiceId и проверьте статус и сумму
    //     $payment = Payment::where('id', $invoiceId)->first();

    //     if (!$payment) {
    //         die("Payment not found");
    //     }

    //     if ($status === 'CANCELED') {
    //         $payment->status = 2;
    //         $payment->save();
    //         die("Payment status CANCELED");
    //     }

    //     if ($status === 'FAILED') {
    //         $payment->status = 2;
    //         $payment->save();
    //         die("Payment status FAILED");
    //     }

    //     if ($payment->sum != $amount) {
    //         $payment->sum = $amount;
    //         $payment->save();
    //     }

    //     if ($payment->status == 1)
    //         die('already paid');


    //     $incrementSum = $payment->sum;

    //     // if ($payment->bonus != 0) {
    //     //     $desc_bank = (($payment->sum * $payment->bonus) / 100) / 5;
    //     //     $this->banking->decrement('bank_dice', $desc_bank);
    //     //     $this->banking->decrement('bank_mines', $desc_bank);
    //     //     $this->banking->decrement('bank_bubbles', $desc_bank);
    //     //     $this->banking->decrement('bank_allin', $desc_bank);
    //     //     $this->banking->decrement('bank_wheel', $desc_bank);
    //     // }

    //     $user = User::find($payment->user_id);

    //     if ($user->balance < 10) {
    //         $user->wager = 0;
    //         $user->save();
    //     }

    //     $incrementSum = $payment->bonus != 0
    //         ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
    //         : $payment->sum;

    //     $user->increment('balance', $incrementSum);
    //      $user->increment('wager', $payment->sum * 3);

    //     if (!is_null($user->referral_use)) {
    //         $this->setReferralProfit($user->id, $payment->sum);
    //     }

    //     $payment->status = 1;
    //     $payment->save();

    //     if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
    //         \Cache::put('user.' . $user->id . '.historyBalance', '[]');
    //     }

    //     $hist_balance = array(
    //         'user_id' => $user->id,
    //         'type' => 'Пополнение через 001MERCHANT',
    //         'balance_before' => $user->balance - $payment->sum,
    //         'balance_after' => round($user->balance, 2),
    //         'date' => date('d.m.Y H:i:s')
    //     );

    //     $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

    //     $cashe_hist_user = json_decode($cashe_hist_user);
    //     $cashe_hist_user[] = $hist_balance;
    //     $cashe_hist_user = json_encode($cashe_hist_user);
    //     \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

    //     $incrementSum = $payment->bonus != 0
    //         ? (($payment->sum * $payment->bonus) / 100)
    //         : 0;
    //     $user->increment('balance', $incrementSum);

    //     if ($payment->bonus != 0) {
    //         $hist_balance = array(
    //             'user_id' => $user->id,
    //             'type' => 'Бонус к депозиту',
    //             'balance_before' => $user->balance - (($payment->sum * $payment->bonus) / 100),
    //             'balance_after' => round($user->balance, 2),
    //             'date' => date('d.m.Y H:i:s')
    //         );

    //         $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

    //         $cashe_hist_user = json_decode($cashe_hist_user);
    //         $cashe_hist_user[] = $hist_balance;
    //         $cashe_hist_user = json_encode($cashe_hist_user);
    //         \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);
    //     }
    //     return 'ok';
    // }

    public function callbackAifory(Request $request, $id)
    {
        $payment = Payment::where('id', $id)->first();

        if (!$payment) {
            return ['error' => 'Order not found'];
        }
        if ($request->statusID != 2) {
            return ['application has not been paid'];
        }
        $user = User::where('id', $payment->user_id)->first();

        if ($request->successPaid > 0 && $payment->status != 1) {
            if ($request->successPaid != $payment->sum) {
                $payment->sum = $request->successPaid;
                $payment->save();
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Пополнение через Aifory',
                'balance_before' => $user->balance - $incrementSum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }

    private function getAiforySBPSingature($order, $userAgent, $fingerprint, $u_created_at)
    {
        $payload = array(
            "amount" => $order->sum,
            "currencyID" => 3,
            "typeID" => 4,
            "TTL" => 999,
            "clientOrderID" => strval($order->id),
            "webhookURL" => "https://stimule1.win/payment/callback-aifory4234234/" . $order->id,
            "extra" => array(
                "comment" => "Оплата заказа #" . $order->id,
                "failedRedirectURL" => env('APP_URL'),
                "successRedirectURL" => env('APP_URL'),
                "payerInfo" => array(
                    "userAgent" => $userAgent,
                    "IP" => $this->getIp(),
                    "userID" => strval($order->user_id),
                    "fingerprint" => $fingerprint,
                    "registeredAt" => $u_created_at,
                ),
                "allowedMethodIDs" => array(
                    10
                )
            )
        );

        $secret = "4lOreuD7rWWDugVmmVtthWewqb2gu7kC795Udd0BWL8sGCX7iWpxjY2xyovdFOGdwVuqwFbI1zmFTn9lMolLKXgb7wUyrFLCLZpaBww5lYeYZkprJNeDORReRt8qZHz2cxk5Tu8lmJ6ZllHSu7dpf4lfDBx13LcC5xjnbgFCnt0XhREuDg3qYC912ff42PtoIBzV3e6SCkIxoltREypndUh0s5edDz2FbWJFdONZOSxGhBgh5DbMMYVoCu87xU8hI9jIZ1Jl3FLtjaSAYtrzhKdtwiVGhlQvl7d4qW1ksz6mE17PS7kcEA51kIFvpcHBnojxpg7rN9hdFjj3SdFv4Nzmel8CX5KcoHtGYbb5rzTRdGnMl6Y2drrtvoHqIoiUWsZX6LSsDtXLq52IhLUkV0Ws7HGko3zzt0ZL848Su775qht5PnWnbnbCdY0CdyTxWoAXe85FFQ2OaqvT5BX1BniqOd95ReaeWV2kQkdSodv4b6VNOQ79cbeedIGRCd1j";

        $payload = json_encode($payload);
        return hash_hmac('sha512', $payload, $secret);
    }

    private function convertCoinToRub($amount, $system)
    {
        $system = strtoupper($system);
        $symbol = $system . 'USDT';

        if ($system == 'USDT') {
            $usdtUrl = 'https://api.binance.com/api/v3/ticker/price?symbol=USDTRUB';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $usdtUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $usdtData = json_decode($response, true);

            if (!isset($usdtData['price'])) {
                return ['error' => 'Error retrieving USDT price data from Binance API'];
            }
            return $amount * $usdtData['price'];
        }
        if ($system == 'TON') {
            // Set your API key
            $apiKey = 'd663334e-bff6-40db-8f54-d55decb14ead';

            // CoinMarketCap API URL
            $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';

            // Parameters for the request
            $parameters = [
                'symbol' => 'TON', // TON symbol
                'convert' => 'RUB', // Convert to USD
            ];

            // Build the query string
            $queryString = http_build_query($parameters);

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt_array($ch, [
                CURLOPT_URL => "$url?$queryString",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Accept: application/json",
                    "X-CMC_PRO_API_KEY: $apiKey"
                ]
            ]);

            // Execute the cURL request
            $response = curl_exec($ch);

            // Check for errors
            if ($response === false) {
                return null;
            }

            // Decode the JSON response
            $data = json_decode($response, true);

            // Check if TON data exists in the response
            if (!isset($data['data']['TON']['quote']['RUB']['price'])) {
                return null;
            }

            // Get the TON price
            $price = $data['data']['TON']['quote']['RUB']['price'];

            // Close cURL
            curl_close($ch);

            return $amount * $price;
        }

        // URL для получения текущего курса USDT к RUB
        $url = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $symbol;

        // Инициализация cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Выполнение запроса и получение ответа
        $response = curl_exec($ch);
        curl_close($ch);

        // Декодирование JSON ответа
        $data = json_decode($response, true);

        // Проверка наличия ключа 'price' в ответе
        if (isset($data['price'])) {
            $amountInUsdt = $data['price'];
            // Переводим USDT в RUB
            $symbol = 'USDTRUB';
            $url = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $symbol;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Выполнение запроса и получение ответа
            $response = curl_exec($ch);
            curl_close($ch);
            // Декодирование JSON ответа
            $data = json_decode($response, true);
            if (isset($data['price'])) {
                return $amountInUsdt * $amount * $data['price'];
            } else {
                return ['error' => 'Unknown price'];
            }
        } else {
            $usdtSymbol = 'USDTRUB';
            $cryptoSymbol = $system . 'USDT';

            // Получение курса RUB к USDT
            $usdtUrl = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $usdtSymbol;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $usdtUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $usdtData = json_decode($response, true);

            if (!isset($usdtData['price'])) {
                return ['error' => 'Error retrieving USDT price data from Binance API'];
            }

            // Получение курса USDT к целевой криптовалюте
            $cryptoUrl = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $cryptoSymbol;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $cryptoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $cryptoData = json_decode($response, true);

            // Конвертация RUB в USDT
            $rubToUsdtRate = $usdtData['price'];
            // $amountInUsdt = $amountInRub / $rubToUsdtRate;

            if (isset($cryptoData['price'])) {
                $usdtToCryptoRate = $cryptoData['price'];
                // amount in usdt
                return $amount * $usdtToCryptoRate * $rubToUsdtRate;
            } else {
                return ['error'];
            }
        }
    }

    private function convertRubToUsdt(Request $request, $amountInRub)
    {
        $crypto = strtoupper($request->system);
        $symbol = $crypto . 'RUB';

        if ($crypto == 'TON') {
            // Set your API key
            $apiKey = 'd663334e-bff6-40db-8f54-d55decb14ead';
            // CoinMarketCap API URL
            $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';

            // Parameters for the request
            $parameters = [
                'symbol' => 'TON', // TON symbol
                'convert' => 'RUB', // Convert to USD
            ];

            // Build the query string
            $queryString = http_build_query($parameters);

            // Initialize cURL
            $ch = curl_init();

            // Set cURL options
            curl_setopt_array($ch, [
                CURLOPT_URL => "$url?$queryString",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Accept: application/json",
                    "X-CMC_PRO_API_KEY: $apiKey"
                ]
            ]);

            // Execute the cURL request
            $response = curl_exec($ch);

            // Check for errors
            if ($response === false) {
                return null;
            }

            // Decode the JSON response
            $data = json_decode($response, true);

            // Check if TON data exists in the response
            if (!isset($data['data']['TON']['quote']['RUB']['price'])) {
                return null;
            }

            // Get the TON price
            $price = $data['data']['TON']['quote']['RUB']['price'];

            // Close cURL
            curl_close($ch);

            return $amountInRub / $price;
        }

        $url = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $symbol;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['price'])) {
            $rubToUsdtRate = $data['price'];
            return $amountInRub / $rubToUsdtRate;
        } else {
            $usdtSymbol = 'USDTRUB';
            $cryptoSymbol = $crypto . 'USDT';

            // Получение курса RUB к USDT
            $usdtUrl = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $usdtSymbol;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $usdtUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $usdtData = json_decode($response, true);

            if (!isset($usdtData['price'])) {
                return ['error' => 'Error retrieving USDT price data from Binance API'];
            }

            // Конвертация RUB в USDT
            $rubToUsdtRate = $usdtData['price'];
            $amountInUsdt = $amountInRub / $rubToUsdtRate;

            // Получение курса USDT к целевой криптовалюте
            $cryptoUrl = 'https://api.binance.com/api/v3/ticker/price?symbol=' . $cryptoSymbol;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $cryptoUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $cryptoData = json_decode($response, true);

            if (isset($cryptoData['price'])) {
                $usdtToCryptoRate = $cryptoData['price'];
                // Конвертация суммы из USDT в целевую криптовалюту
                return $amountInUsdt / $usdtToCryptoRate;
            } else {
                return ['error'];
            }
        }
    }

    public function createOrderAiforyCrypto(Request $request, $order_id)
    {
        if ($request->amount < 500) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения 500 руб'
            ];
        }
        $user = User::where('id', $request->user()->id)->first();

        $request->amount = $this->convertRubToUsdt($request, $request->amount);
        // dd($this->convertCoinToRub($request->))
        if (!$user) {
            return 'error';
        }

        $userAgent = '84U{Yk';
        $apiKey = 'uPRnt6yeXXSOcHGW7XKlHnJkmTtpCqqqChEmyO3YUfB6oKRwNjTpJ9hUIdrzKzPrAwrNUqhIGtG4fm1bilNCWOmDkva6XgLrO8qRIYZooaaFRE8g4GXFuQxikYhmGyTj9d6aiLTpYle3pJhg79TWAFg8a7M4zS2mm1sWGN6wOgCDbKKi4QztH5lxJOuC8NddEAgW0B1DR0y9nHuwAJqUmlHzu9jkcHmw3KBTN8mPHWzeTB0tClZLDAHw60iAdguf';
        // dd($_SERVER['X-Forwarded-For']);
        $ua = $request->header('User-Agent');
        $fingerprint = Hash::make($this->getIp() . $userAgent);

        $order = Payment::where('id', $order_id)->first();
        $coin_id = null;
        if ($request->system == 'usdt' && $request->network == 'trc-20') {
            $coin_id = 5;
        }
        if ($request->system == 'usdt' && $request->network == 'polygon') {
            $coin_id = 1009;
        }
        if ($request->system == 'usdt' && $request->network == 'erc-20') {
            $coin_id = 1029;
        }
        if ($request->system == 'usdt' && $request->network == 'bep-20') {
            $coin_id = 1015;
        }

        if ($request->system == 'trx') {
            $coin_id = 1025;
        }
        if ($request->system == 'ton') {
            $coin_id = 1021;
        }
        if ($request->system == 'bnb') {
            $coin_id = 1008;
        }

        $order->merchant_meta = $coin_id;
        $order->save();

        $sign = $this->getAiforyCryptoSingature(
            $order,
            $ua,
            $fingerprint,
            strtotime($user->created_at),
            $coin_id,
            $request->amount
        );

        // return ['sign' => $sign, 'userAgent' => $userAgent, 'fingerprint' => $fingerprint];

        $endpoint = 'https://api.euphoria.inc/payin/process';

        $headers = [
            'Content-Type' => 'application/json',
            'Signature' => $sign,
            'user-agent' => $userAgent,
            'API-Key' => $apiKey
        ];

        $client = new Client([
            'headers' => $headers
        ]);


        $r = $client->post($endpoint, [
            'body' => json_encode([
                "amount" => $request->amount,
                2,
                "currencyID" => $coin_id,
                "typeID" => 8,
                "clientOrderID" => strval($order->id),
                "TTL" => 50000,
                "webhookURL" => "https://stimule1.win/payment/callback-aifory83457432/" . $order->id,
                "extra" => [
                    "comment" => "Оплата заказа #" . $order->id,
                    "failedRedirectURL" => env('APP_URL'),
                    "successRedirectURL" => env('APP_URL'),
                    "payerInfo" => [
                        "userAgent" => $ua,
                        "IP" => $this->getIp(),
                        "userID" => strval($order->user_id),
                        "registeredAt" => strtotime($user->created_at),
                    ]
                ]
            ])
        ]);

        $response = json_decode($r->getBody()->getContents());

        \Log::debug([$response]);

        return [$response->paymentURL];
    }

    public function callbackAiforyCrypto(Request $request, $id)
    {
        $payment = Payment::where('id', $id)->first();

        if (!$payment) {
            return ['error' => 'Order not found'];
        }
        if (!$payment->merchant_meta) {
            return ['error' => 'Coin ID not found'];
        }

        $user = User::where('id', $payment->user_id)->first();

        $request->successPaid = $this->convertCoinToRub($request->successPaid, $payment->system);

        if ($request->successPaid > 0 && $payment->status != 1) {
            if ($request->successPaid != $payment->sum) {
                $payment->sum = $request->successPaid;
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, round($payment->sum, 2));
            }
            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);

            $user->increment('balance', $incrementSum);

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Пополнение через Aifory',
                'balance_before' => $user->balance - $incrementSum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $payment->status = 1;
            $payment->save();
        }
    }


    public function handle(Request $request)
    {
        $terminalId = config('api-clients.fk.terminal_id');
        $terminalSecret2 = config('api-clients.fk.terminal_secret_2', config('api-clients.fk.terminal_secret_1'));
        
        $sign = md5(
            $terminalId . ':' . $request->AMOUNT . ':' . $terminalSecret2 . ':' . $request->MERCHANT_ORDER_ID
        );
        if ($sign != $request->SIGN) {
            return 'wrong sign';
        }

        $payment = Payment::find($request->MERCHANT_ORDER_ID);

        if (!$payment) {
            return 'payment not found';
        }

        if ($payment->status) {
            return 'payment already paid';
        }

        $incrementSum = $payment->bonus != 0
            ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
            : $payment->sum;

        $user = User::find($payment->user_id);

        if ($user->balance < 10) {
            $user->wager = 0;
            $user->save();
        }

        $user->increment('balance', ($incrementSum + $incrementSum * 0.05));
        $user->increment('wager', $incrementSum * $payment->wager);

        if (!is_null($user->referral_use)) {
            $this->setReferralProfit($user->id, $payment->sum);
        }

        $payment->status = 1;
        $payment->save();

        Action::create([
            'user_id' => $user->id,
            'action' => 'Пополнение через Free-kassa',
            'balanceBefore' => $user->balance - $payment->sum,
            'balanceAfter' => round($user->balance, 2)
        ]);

        return 'YES';
    }

    public function paymentSuccess(Request $request)
    {
        $paymentId = $request->get('payment_id');
        
        if (!$paymentId) {
            return redirect('/')->with('error', 'Платеж не найден');
        }

        $payment = Payment::find($paymentId);
        
        if (!$payment) {
            return redirect('/')->with('error', 'Платеж не найден');
        }

        // Обновляем сессию пользователя, если он авторизован
        if (auth()->check() && auth()->id() == $payment->user_id) {
            // Обновляем данные пользователя в сессии
            $user = auth()->user();
            $user->refresh();
            
            // Обновляем сессию
            session()->put('user', $user);
        }

        // Проверяем статус платежа
        if ($payment->status == 1) {
            // Платеж успешно обработан
            return redirect('/')->with('success', 'Платеж успешно обработан! Баланс пополнен.');
        }

        // Платеж еще обрабатывается
        return redirect('/')->with('info', 'Платеж обрабатывается. Баланс будет пополнен в ближайшее время.');
    }

    public function serjCancel(Request $request)
    {
        $endpoint = 'https://p2p-paradise.info/api/v2/payment/cancel';
        $secretKey = 'c42da151c66201841587cc1630550866eed0df9cca8be9eb8695eef76d392bc6';
        $data = [
            "id" => $request->id,
            "request_proprietary_id" => $request->request_proprietary_id
        ];
        $response = Http::withHeaders([
            'X-Auth-Key' => $secretKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, $data);

        $responseArray = $response->json(true);
        return ['response' => $responseArray];
    }

    public function serjConfirm(Request $request)
    {
        $endpoint = 'https://p2p-paradise.info/api/v2/payment/confirm';
        $secretKey = 'c42da151c66201841587cc1630550866eed0df9cca8be9eb8695eef76d392bc6';
        $data = [
            "id" => $request->id,
            "request_proprietary_id" => $request->request_proprietary_id
        ];
        $response = Http::withHeaders([
            'X-Auth-Key' => $secretKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, $data);

        $responseArray = $response->json(true);
        return ['response' => $responseArray];
    }

    public function serjPayCallback(Request $request)
    {
        \Log::debug($request);
        $payment = Payment::where('merchant_meta', $request->request_proprietary_id)->first();
        $withdraw = Withdraw::where('id', $request->merchant_request_id)->first();
        if (!$payment && !$withdraw) {
            return ['error' => 'Order not found'];
        }

        if ($withdraw && $request->type == 'sell') {
            if ($request->status == 'paid') {
                $withdraw->status = 1;
                $withdraw->save();
            }
            if ($request->status == 'cancelled') {
                $withdraw->status = 2;
                $withdraw->save();
            }
            if ($request->status == 'failed') {
                $withdraw->status = 2;
                $withdraw->save();
            }
            \Log::debug($withdraw);
            return 'OK';
        }

        if ($request->status == "SUCCESS") {
            if ($payment->status == 1) {
                return ['application already been paid'];
            }

            $user = User::where('id', $payment->user_id)->first();

            if ($request->amount_to_pay != $payment->sum) {
                $payment->sum = $request->amount_to_pay;
                $payment->save();
            }

            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Пополнение через SerjPay',
                'balance_before' => $user->balance - $incrementSum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $payment->status = 1;
            $payment->save();

            return ['ok'];
        } else {
            if ($request->status == 'FAILED') {
                $payment->status = 2;
                $payment->save();
                return ['application has not been paid'];
            }
        }
    }

    private function createOrderH2h($request, $bonus = 0)
    {
        $endpoint = 'https://p2p-paradise.info/api/v2/payment/create';
        $secretKey = 'c42da151c66201841587cc1630550866eed0df9cca8be9eb8695eef76d392bc6';
        $user = User::find($request->user()->id);
        if (!$user) {
            return ['error' => true, "message" => "Пользователь не найден"];
        }

        if ($request->amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения 100 руб!'
            ];
        }

        $code = $request->code;
        $wager = 3;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $payment = Payment::create([
            'user_id' => $user->id,
            'sum' => $request->amount,
            'bonus' => $bonus,
            'wager' => $wager,
            'system' => $request->system,
        ]);

        $data = [
            'client' => $user->id, // предполагается, что client_id передается в запросе
            'merchant_request_id' => $payment->id, // предполагается, что order_id передается в запросе
            'initial_amount' => $payment->sum // предполагается, что сумма передается в запросе
        ];

        // Отправляем запрос
        $response = Http::withHeaders([
            'X-Auth-Key' => $secretKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, $data);

        $responseArray = $response->json(true);
        $responseArray = $responseArray['data'];
        $dublicat = Payment::where('merchant_meta', $responseArray['request_proprietary_id'])->first();
        if ($dublicat) {
            $payment->delete();
        } else {
            if (!$responseArray['request_proprietary_id']) {
                $payment->delete();
                return;
            } else {
                $payment->merchant_meta = $responseArray['request_proprietary_id'];
                $payment->save();
            }
        }

        return [
            'request_proprietary_id' => $responseArray['request_proprietary_id'],
            'merchant_request_id' => $responseArray['merchant_request_id'],
            'initial_amount' => $responseArray['initial_amount'],
            'amount_to_pay' => $responseArray['amount_to_pay'],
            'created' => $responseArray['created_at'],
            'bank_account' => $responseArray['bank_account'],
            'bank_name' => $responseArray['bank_name'],
            'name' => $responseArray['receipt'],
        ];
    }

    private function createOrder($request, $bonus = 0)
    {
        if ($request->amount < 10) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения 10 руб'
            ];
        }
        if ($request->user()->limit_payment) {
            return [
                'error' => true,
                'message' => 'Платежи ограничены'
            ];
        }

        if ($request->system == '001MerCard' || $request->system == '001MerSBP') {
            $userId = $request->user()->id;

            $today = Carbon::today();

            $startOfDay = Carbon::today();
            $endOfDay = Carbon::tomorrow()->subSecond();

            $latestOrders = Payment::where('user_id', $userId)
                ->whereIn('system', ['001MerCard', '001MerSBP'])
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $unpaidCount = 0;
            $latestUnpaidDate = null;

            foreach ($latestOrders as $order) {
                if ($order->status == 0) {
                    $unpaidCount++;
                    if (is_null($latestUnpaidDate) || $order->created_at->gt($latestUnpaidDate)) {
                        $latestUnpaidDate = $order->created_at;
                    }
                }
            }

            if ($unpaidCount >= 5) {
                $oneDayAgo = Carbon::now()->subHours(24);
                if ($latestUnpaidDate && $latestUnpaidDate->gt($oneDayAgo)) {
                    return [
                        'error' => true,
                        'message' => 'Попробуйте использовать другой метод пополнения'
                    ];
                }
            }
        }


        $code = $request->code;
        $wager = 3;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $amount = $request->amount;

        $payment = Payment::create([
            'user_id' => $this->user->id,
            'sum' => intval($amount),
            'bonus' => $bonus,
            'wager' => $wager,
            'system' => $request->provider
        ]);


        return $payment;
    }

    private function createOrderAifory($request, $bonus = 0)
    {
        if ($request->amount < 2000) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения 2000 руб'
            ];
        }
        // $payments = count(Payment::where('user_id', $request->user()->id)->where("status", 1)->get());
        // $user = User::find($request->user()->id);
        // $withdraws = count(Withdraw::where('user_id', $request->user()->id)->where("status", 1)->get());

        // if (!Carbon::createFromFormat('Y-m-d H:i:s', $user->created_at)->addMonth()->lte(Carbon::now()) || $payments < 5 || $withdraws < 3) 
        // {
        //     return [
        //         'error' => true,
        //         'message' => 'Попробуйте другой метод пополнения'
        //     ];
        // }

        $code = $request->code;
        $wager = 3;

        // if (date('D') == 'Sun' && $request->amount >= 300) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        // // Получаем целочисленную часть из параметра 'amount'
        // $amount = intval($request->amount);

        // // Генерируем случайное число от 1 до 99 для копеек
        // $randomCents = rand(1, 99);

        // // Добавляем копейки к целой части
        // $totalAmount = $amount + $randomCents / 100;

        $payment = Payment::create([
            'user_id' => $this->user->id,
            'sum' => $request->amount,
            'bonus' => $bonus,
            'wager' => $wager,
            'system' => $request->system
        ]);

        return $payment;
    }

    private function setReferralProfit($user_id, $amount)
    {
        $user = User::find($user_id);
        $amount = $amount / 100;

        DB::beginTransaction();

        @$referral_1_lvl = User::find($user->referral_use);
        @$referral_2_lvl = User::find($referral_1_lvl->referral_use);
        @$referral_3_lvl = User::find($referral_2_lvl->referral_use);

        if (!is_null($referral_1_lvl)) {
            $percent = 10;

            if ($referral_1_lvl->ref_1_lvl > 0) {
                $percent = $referral_1_lvl->ref_1_lvl;
            }

            $referral_1_lvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referral_1_lvl->id,
                'amount' => $amount * $percent,
                'level' => 1
            ]);
        }

        if (!is_null($referral_2_lvl)) {
            $percent = 3;

            if ($referral_2_lvl->ref_2_lvl > 0) {
                $percent = $referral_2_lvl->ref_2_lvl;
            }

            $referral_2_lvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referral_2_lvl->id,
                'amount' => $amount * $percent,
                'level' => 2
            ]);
        }

        if (!is_null($referral_3_lvl)) {
            $percent = 2;

            if ($referral_3_lvl->ref_3_lvl > 0) {
                $percent = $referral_3_lvl->ref_3_lvl;
            }

            $referral_3_lvl->increment('referral_balance', $amount * $percent);

            ReferralProfit::create([
                'from_id' => $user->id,
                'ref_id' => $referral_3_lvl->id,
                'amount' => $amount * $percent,
                'level' => 3
            ]);
        }

        DB::commit();

        return true;
    }

    private function getParams($url, $params = []): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $html = curl_exec($ch);
        curl_close($ch);

        @$DOM = new \DOMDocument;
        @$DOM->loadHTML($html);

        $inputs = $DOM->getElementsByTagName('input');
        $response = [];

        foreach ($inputs as $input) {
            $name = $input->getAttribute('name');

            if (in_array($name, $params) && !isset($response[$name])) {
                $response[$name] = $input->getAttribute('value');
            }
        }

        return $response;
    }

    public function workerBalance()
    {
        if (!$this->user->is_worker) {
            return [
                'error' => true,
                'message' => 'У вас нет доступа'
            ];
        }

        if ($this->user->balance >= 3000) {
            return [
                'error' => true,
                'message' => 'Баланс должен быть меньше 3000р'
            ];
        }

        $this->user->increment('balance', 1000);

        return [
            'balance' => $this->user->balance
        ];
    }

    public function createNicePay(Request $request, $bonus = 0)
    {
        $user = User::find($request->user()->id);
        $amount = floatval($request->amount);
        $totalAmount = intval($amount * 100);

        $successful_orders = Payment::where("user_id", $user->id)
            ->where("status", 1)
            ->get();

        $last_orders = Payment::where("user_id", $user->id)
            ->where("system", "nicepay")
            ->whereDate("created_at", Carbon::today())
            ->orderBy("id", "desc")
            ->get();

        if (count($last_orders) >= 4) {
            if (
                $last_orders[0]->status != 1 &&
                $last_orders[1]->status != 1 &&
                $last_orders[2]->status != 1 &&
                $last_orders[3]->status != 1
            ) {
                return [
                    'error' => true,
                    'message' => 'Воспользуйтесь другим методом пополнения'
                ];
            }
        }

        $code = $request->code;
        $wager = 3;

        // if (date('D') == 'Sun' && $request->amount >= 300) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        // // Получаем целочисленную часть из параметра 'amount'
        // $amount = intval($request->amount);

        // // Генерируем случайное число от 1 до 99 для копеек
        // $randomCents = rand(1, 99);

        // // Добавляем копейки к целой части
        // $totalAmount = $amount + $randomCents / 100;

        $order = Payment::create([
            'user_id' => $user->id,
            'sum' => $request->amount,
            'bonus' => $bonus,
            'wager' => $wager,
            'system' => $request->system
        ]);

        $params = [
            'merchant_id' => '66d8414fbf04a2341cd77f97', // ID мерчанта из .env файла
            'secret' => 'j77L3-FyYo0-rNXkV-dR7F3-OwNGC', // Secret Key из .env файла
            'order_id' => $order->id,
            'customer' => $user->id,
            'amount' => $totalAmount,
            'currency' => 'RUB',
            'description' => 'Оплата счета #' . $order->id,
            'success_url' => 'https://stimule1.win',
            'fail_url' => 'https://stimule1.win',
        ];

        // Отправка запроса
        $response = Http::post('https://nicepay.io/public/api/payment', $params);
        $data = json_decode($response);

        // Обработка ответа
        if ($data->status == 'success') {
            // Успешный ответ
            $order->merchant_meta = $data->data->payment_id;
            $order->save();

            return ['url' => $data->data->link];
        } else {
            // Обработка ошибки
            return response()->json([
                'error' => $response->body(),
            ], $response->status());
        }
    }

    public function callbackAiforyWithdraw(Request $request)
    {
        \Log::debug($request);

        $postData = $request->all();
        $clientOrderID = intval($postData['clientOrderID']);
        $statusID = $postData['statusID'];

        $withdraw = Withdraw::find($clientOrderID);

        if (!$withdraw) {
            return response()->json(['success' => 'false', 'msg' => 'Order not found']);
        }

        if ($statusID == 2) {
            $withdraw->status = 1;
            $withdraw->save();
        } elseif ($statusID == 3) {
            $user = User::where('id', $withdraw->user_id)->lockForUpdate()->first();
            $user->balance += $withdraw->sum;
            $user->save();
            $withdraw->reason = "Отказ банка. Повторите попытку или измените реквизиты.";
            $withdraw->status = 2;
            $withdraw->save();

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Отмена выплаты (Aifory)',
                'balance_before' => $user->balance - $withdraw->sum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            return response()->json(['success' => true, 'balance' => $user->balance]);
        }

        return response()->json(['success' => 'true']);
    }


    public function createParadise(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = $request->amount;

        if (auth()->user()->limit_payment) {
            return [
                'error' => true,
                'message' => 'Платежи ограничены'
            ];
        }

        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 100р.'
            ];
        }

        $amount = intval($request->amount * 100);

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => intval($request->amount),
            "system" => PaymentProvidersEnum::PARADISE->value,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        // Данные для аутентификации
        $shop_id = config('api-clients.paradise.shop_id');
        $secret = config('api-clients.paradise.api_secret');

        // Получаем данные из запроса
        $merchantOrderId = $order->id;
        $userId = $user->id;
        $email = $userId . "@paradise.info";

        // URL API
        $apiUrl = config('api-clients.paradise.base_url') . 'payments';

        // Подготовка тела запроса
        $payload = [
            'merchant_customer_id' => (string)$merchantOrderId,
            'return_url' => config('app.url'),
            'amount' => $amount,
            'description' => $email,
		//	'payment_method'=> 'sbp',
        ];

        \Log::debug($payload);

        // Инициализация cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "merchant-id: {$shop_id}",
                "merchant-secret-key: {$secret}",
                "Content-Type: application/json",
            ],
        ]);

        // Выполнение запроса
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // Проверка на ошибку
        if ($err) {
            return response()->json(['error' => 'cURL Error: ' . $err], 500);
        }

        // Преобразование ответа в массив
        $responseData = json_decode($response, true);

        \Log::debug($responseData);

        // Возврат результата
        if (!empty($responseData['status']) && $responseData['status'] === 'waiting') {
            $order->update(['merchant_meta' => $responseData['uuid']]);
            return response()->json([
                'success' => true,
                'payment' => [
                    'receiver' => $responseData['payment_method']['phone'],
                    'name' => $responseData['payment_method']['name'],
                    'bank' => $responseData['payment_method']['bank'],
                    'amount' => $responseData['amount'] / 100
                ]
            ]);
        }

        return ['error' => true, 'message' => array_column($responseData['errors'], 'message')[0]];
    }

    public function callbackParadise(Request $request)
    {
        \Log::debug('Paradise calback', $request->toArray());

        if (str_starts_with($request['type'], 'payment.')) {
            $id = intval($request['object']['merchant_customer_id']);
            $payment = Payment::where('id', $id)->first();
            $amount = $request['object']['amount'] / 100;

            if (!$payment) {
                return ['error' => 'Order not found'];
            }

            $user = User::where('id', $payment->user_id)->first();

            if ($request['type'] == 'expired') {
                $payment->status = 2;
                $payment->save();
                return ['ok'];
            }

            if ($amount > 0 && $payment->status != 1 && $request['type'] == 'payment.success') {
                if ($amount != $payment->sum) {
                    $payment->sum = $amount;
                    $payment->save();
                }
                if (!is_null($user->referral_use)) {
                    $this->setReferralProfit($user->id, $payment->sum);
                }

                $incrementSum = $payment->bonus != 0
                    ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                    : $payment->sum;

                $user->increment('wager', $payment->sum * 3);
                $user->increment('balance', $incrementSum);

                if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                    \Cache::put('user.' . $user->id . '.historyBalance', '[]');
                }

                $hist_balance = array(
                    'user_id' => $user->id,
                    'type' => 'Пополнение через Paradise',
                    'balance_before' => $user->balance - $incrementSum,
                    'balance_after' => round($user->balance, 2),
                    'date' => date('d.m.Y H:i:s')
                );

                $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

                $cashe_hist_user = json_decode($cashe_hist_user);
                $cashe_hist_user[] = $hist_balance;
                $cashe_hist_user = json_encode($cashe_hist_user);
                \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

                $payment->status = 1;
                $payment->save();
            }
            return ['ok'];
        }

        if (str_starts_with($request['type'], 'payout.')) {
            $id = $request['object']['metadata']['orderNumber'];
            $withdraw = Withdraw::where('id', $id)->first();
            $amount = intval($request['object']['amount'] / 100);
            if (!$withdraw) {
                return ['error' => 'Order not found'];
            }

            $user = User::where('id', $withdraw->user_id)->first();

            if ($request['type'] == 'payout.error') {
                $withdraw->update(['reason' => 'Отказ банка1.', 'status' => 2]);
                $user->increment('balance', $amount);
                $user->save();


                if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                    \Cache::put('user.' . $user->id . '.historyBalance', '[]');
                }

                $hist_balance = array(
                    'user_id' => $user->id,
                    'type' => 'Отмена вывода (отказ банка)',
                    'balance_before' => round($user->balance + $withdraw->sum, 2),
                    'balance_after' => round($user->balance, 2),
                    'date' => date('d.m.Y H:i:s')
                );

                $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

                $cashe_hist_user = json_decode($cashe_hist_user);
                $cashe_hist_user[] = $hist_balance;
                $cashe_hist_user = json_encode($cashe_hist_user);
                \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

                return [$user->balance . ' ok: ' . $withdraw];
            }


            if ($request['type'] == 'payout.success') {
                if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                    \Cache::put('user.' . $user->id . '.historyBalance', '[]');
                }

                $hist_balance = array(
                    'user_id' => $user->id,
                    'type' => 'Вывод через Paradise',
                    'balance_before' => $user->balance + $amount,
                    'balance_after' => round($user->balance, 2),
                    'date' => date('d.m.Y H:i:s')
                );

                $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

                $cashe_hist_user = json_decode($cashe_hist_user);
                $cashe_hist_user[] = $hist_balance;
                $cashe_hist_user = json_encode($cashe_hist_user);
                \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);
                $withdraw->update(['status' => 1]);

                return ['ok-success: ' . $withdraw];
            }
        }
    }

    public function createEightpay(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = $request->amount;

        if ($amount < 500) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 500р.'
            ];
        }

        $amount = intval($request->amount * 100);

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => intval($request->amount),
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);


        // Данные для аутентификации
        $merchant_private_key = '523e49fdb6d25521f617'; // Ваш секретный ключ

        // Получаем данные из запроса
        $merchantOrderId = $order->id;
        $userId = $user->id;
        $email = $userId . "@eightpay.info";

        // URL API
        $apiUrl = 'https://business.processinprocess.com/api/v1/payments';

        // Подготовка тела запроса
        $payload = [
            'product' => (string)$merchantOrderId,
            'amount' => $amount,
            'currency' => 'RUB',
            'callbackUrl' => config('app.url') . '/callback/eightpay',
            'redirectSuccessUrl' => config('app.url') . '/pay',
            'redirectFailUrl' => config('app.url') . '/pay',
            'orderNumber' => (string)$merchantOrderId,
            'extra_return_param' => 'SBP_aquiring',
            "customer" => [
                'email' => $email,
                'ip' => $request->ip(),
            ],

        ];

        \Log::debug($payload);

        // Инициализация cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$merchant_private_key}",
                "Content-Type: application/json",
            ],
        ]);

        // Выполнение запроса
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // Проверка на ошибку
        if ($err) {
            return response()->json(['error' => 'cURL Error: ' . $err], 500);
        }

        // Преобразование ответа в массив
        $responseData = json_decode($response, true);

        // return ['error' => true, 'message' =>  $responseData];

        \Log::debug($responseData);

        // Возврат результата
        if (!empty($responseData['success']) && $responseData['success'] === true) {
            $order->update(['merchant_meta' => $responseData['token']]);

            $url = $responseData['processingUrl'][0]['GYDANSPK'];

            return response()->json([
                'success' => true,
                'url' => $url, // Ссылка на форму оплаты
            ]);
        }

        return ['error' => true, 'message' => $responseData];
    }

    public function callbackEightpay(Request $request)
    {
        \Log::debug($request);


        $id = $request['token'];
        $payment = Payment::where('merchant_meta', $id)->first();
        $amount = $request['data']['amount'] / 100;

        if (!$payment) {
            return ['error' => 'Order not found'];
        }

        $user = User::where('id', $payment->user_id)->first();

        if ($request['status'] == 'declined') {
            $payment->status = 2;
            $payment->save();
            return ['ok'];
        }

        if ($amount > 0 && $payment->status != 1 && $request['status'] == 'approved') {
            // if ($amount != $payment->sum) {
            //     $payment->sum = $amount;
            //     $payment->save();
            // }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Пополнение через Paradise',
                'balance_before' => $user->balance - $incrementSum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }

    public function createGrow(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }
        $successful_orders = Payment::where("user_id", $user->id)
            ->where("status", 1)
            ->get();

        if (count($successful_orders) < 3) {
            return [
                'error' => true,
                'message' => 'Воспользуйтесь другим методом пополнения!'
            ];
        }

        $amount = $request->amount;

        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 100р.'
            ];
        }

        $amount = $request->amount;

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => intval($amount),
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        // Данные для аутентификации
        $signature = 'd75ed6f7-50ab-42c7-bbaf-7c794aaaa76b'; // Ваш секретный ключ

        // URL API
        $apiUrl = 'https://grow-bank.io/wallet/order/create';

        // Подготовка тела запроса
        $payload = [
            'signature' => $signature,
            'amount' => $amount,
        ];

        \Log::debug($payload);

        // Инициализация cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
            ],
        ]);

        // Выполнение запроса
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // Проверка на ошибку
        if ($err) {
            return response()->json(['error' => 'cURL Error: ' . $err], 500);
        }

        // Преобразование ответа в массив
        $responseData = json_decode($response, true);

        // return ['error' => true, 'message' =>  $responseData];

        \Log::debug($responseData);

        // Возврат результата
        if (!empty($responseData['status']) && $responseData['status'] === 'Pending') {
            $order->update(['merchant_meta' => $responseData['order_id']]);

            $url = $responseData['urlv2'];

            return response()->json([
                'success' => true,
                'url' => $url, // Ссылка на форму оплаты
            ]);
        }

        return ['error' => true, 'message' => $responseData];
    }

    public function callbackGrow(Request $request)
    {
        \Log::debug($request);

        // [2025-06-27 07:07:19] local.DEBUG: array (
        //     'order_id' => 'O605195992-27060706',
        //     'status' => 'Paid',
        //     'amount' => 100,
        //     'description' => 'Order O605195992-27060706',
        //   )  


        $id = $request['order_id'];
        $payment = Payment::where('merchant_meta', $id)->first();
        $amount = $request['amount'];

        if (!$payment) {
            return ['error' => 'Order not found'];
        }

        $user = User::where('id', $payment->user_id)->first();

        if ($request['status'] == 'Expired') {
            $payment->status = 2;
            $payment->save();
            return ['ok'];
        }

        if ($amount > 0 && $payment->status != 1 && $request['status'] == 'Paid') {
            // if ($amount != $payment->sum) {
            //     $payment->sum = $amount;
            //     $payment->save();
            // }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;


            $user->increment('wager', $incrementSum * 3);
            $user->increment('balance', $incrementSum);

            Action::create([
                'user_id' => $user->id,
                'action' => 'Пополнение через Grow',
                'balanceBefore' => $user->balance - $incrementSum,
                'balanceAfter' => round($user->balance, 2)
            ]);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }


    public function createGtx(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return [
                'error' => true,
                'message' => 'Ваш аккаунт заблокирован'
            ];
        }
        if ($user->limit_payment) {
            return [
                'error' => true,
                'message' => 'Платежи ограничены'
            ];
        }

        $amount = $request->amount;

        if ($request->system == 'nspk') {
            if ($amount < 1000) {
                return [
                    'error' => true,
                    'message' => 'Минимальная сумма пополнения для выбранного метода 1000р.'
                ];
            }
        } else {
            if ($request->system == 'spay') {
                if ($amount < 500) {
                    return [
                        'error' => true,
                        'message' => 'Минимальная сумма пополнения для выбранного метода 500р.'
                    ];
                }
            } else {
                if ($request->system == 'c2c') {
                    if ($amount < 3000) {
                        return [
                            'error' => true,
                            'message' => 'Минимальная сумма пополнения для выбранного метода 3000р.'
                        ];
                    }
                } else {
                    if ($amount < 500) {
                        return [
                            'error' => true,
                            'message' => 'Минимальная сумма пополнения для выбранного метода 500р.'
                        ];
                    }
                }
            }
        }


        $amount = intval($request->amount);

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => intval($request->amount),
            "system" => 'gtx',
            'wager' => $wager,
            'bonus' => $bonus,
        ]);


        $token = '47|lfbXLyHHP0EOI7ievK6f22isT67rFa0nHJR7mcvy';
        $secretKey = '4beee862-23af-4cda-a397-bd2f33281cc3';
        $code = 'diyq0qd3ww2kwfzwt22kxbrc';

        // URL API
        $apiUrl = 'https://gtxpay.pro/api/v2/payments';

        // Подготовка тела запроса
        $payload = [
            'amount' => $amount,
            'callbackUri' => config('app.url') . '/callback/gtx',
            'currency' => 'RUB',
            'failUri' => config('app.url') . '/pay',
            'merchantId' => $code,
            'orderId' => (string)$order->id,
            'userId' => (string)$user->id,
            'method' => $request->system
        ];

        $payloadJson = json_encode($payload);

        $signature = hash_hmac(
            'sha256',
            $payloadJson,
            $secretKey
        );


        \Log::debug($payload);

        // Инициализация cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$token}",
                "Signature: {$signature}",
                "Content-Type: application/json",
            ],
        ]);

        // Выполнение запроса
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // Проверка на ошибку
        if ($err) {
            return response()->json(['error' => 'cURL Error: ' . $err], 500);
        }

        // Преобразование ответа в массив
        $responseData = json_decode($response, true);

        // Проверка на ошибки в ответе API
        if (isset($responseData['error']) || isset($responseData['errors']) || $httpCode >= 400) {
            $errorMessage = $responseData['error']['message'] ?? 
                          $responseData['message'] ?? 
                          $responseData['error'] ?? 
                          (isset($responseData['errors']) ? json_encode($responseData['errors']) : null) ??
                          'Не удалось создать платеж';
            
            \Log::error('GTX API Error', [
                'http_code' => $httpCode,
                'error' => $errorMessage,
                'response' => $responseData,
            ]);
            
            return [
                'error' => true,
                'message' => $errorMessage
            ];
        }

        // Возврат результата
        if (!empty($responseData['result']['state']) && $responseData['result']['state'] == 'pending') {
            $order->update(['merchant_meta' => $responseData['result']['id']]);
            return response()->json([
                'success' => true,
                'url' => $responseData['url'], // Ссылка на форму оплаты
            ]);
        }

        // Если не удалось определить причину, возвращаем общее сообщение
        \Log::warning('GTX unexpected response', [
            'response' => $responseData,
            'http_code' => $httpCode,
        ]);

        return [
            'error' => true,
            'message' => $responseData['message'] ?? 'Попробуйте попытку через 15 минут или используйте другой метод'
        ];
    }

    public function callbackGtx(Request $request)
    {
        \Log::debug($request);

        $id = intval($request['orderId']);
        $payment = Payment::where('id', $id)->first();
        $amount = $request['amount'];

        if (!$payment) {
            return ['error' => 'Order not found'];
        }

        $user = User::where('id', $payment->user_id)->first();

        if ($request['state'] == 'expired' || $request['state'] == 'canceled') {
            $payment->status = 2;
            $payment->save();
            return ['ok'];
        }

        if ($amount > 0 && $payment->status != 1 && $request['state'] == 'finished') {
            if ($amount != $payment->sum) {
                $payment->sum = $amount;
                $payment->save();
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            Action::create([
                'user_id' => $user->id,
                'action' => 'Пополнение через GTX',
                'balanceBefore' => $user->balance - $incrementSum,
                'balanceAfter' => round($user->balance, 2)
            ]);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }

    public function callbackCryptobot(Request $request)
    {
        \Log::info('Cryptobot callback received', [
            'all_data' => $request->all(),
            'payload' => $request->input('payload'),
            'raw' => $request->getContent()
        ]);

        // Поддерживаем разные форматы callback от Cryptobot
        $payload = $request->input('payload', $request->all());
        
        // Если payload не массив, значит данные пришли напрямую
        if (!is_array($payload)) {
            $payload = $request->all();
        }

        // Получаем invoice_id из разных возможных мест
        $invoiceId = $payload['invoice_id'] ?? $payload['id'] ?? $request->input('invoice_id') ?? null;
        
        if (!$invoiceId) {
            \Log::error('Cryptobot callback: invoice_id not found', [
                'request' => $request->all(),
                'payload' => $request->input('payload')
            ]);
            return ['error' => 'invoice_id not found'];
        }

        // Ищем платеж по merchant_meta (пробуем и строку, и число)
        $payment = Payment::where(function($query) use ($invoiceId) {
            $query->where('merchant_meta', (string)$invoiceId)
                  ->orWhere('merchant_meta', (int)$invoiceId);
        })->first();

        if (!$payment) {
            \Log::error('Cryptobot callback: payment not found', [
                'invoice_id' => $invoiceId,
                'invoice_id_string' => (string)$invoiceId,
                'invoice_id_int' => (int)$invoiceId,
                'recent_cryptobot_payments' => Payment::where('system', 'cryptobot')
                    ->whereNotNull('merchant_meta')
                    ->orderBy('id', 'desc')
                    ->limit(5)
                    ->get(['id', 'merchant_meta', 'status', 'sum'])
                    ->toArray()
            ]);
            return ['error' => 'Order not found'];
        }

        \Log::info('Cryptobot callback: payment found', [
            'payment_id' => $payment->id,
            'invoice_id' => $invoiceId,
            'merchant_meta' => $payment->merchant_meta,
            'current_status' => $payment->status
        ]);

        $user = User::where('id', $payment->user_id)->first();
        
        if (!$user) {
            \Log::error('Cryptobot callback: user not found', ['payment_id' => $payment->id]);
            return ['error' => 'User not found'];
        }

        // Получаем статус и сумму из payload
        $status = $payload['status'] ?? $request->input('status') ?? null;
        $amount = $payload['amount'] ?? $request->input('amount') ?? $payment->sum;

        // Обработка статуса 'expired'
        if ($status === 'expired') {
            $payment->status = 2;
            $payment->save();
            \Log::info('Cryptobot callback: payment expired', ['payment_id' => $payment->id]);
            return ['ok'];
        }

        // Проверяем статус 'paid', 'completed' или 'success' (в разных регистрах)
        $isPaid = in_array(strtolower($status ?? ''), ['paid', 'completed', 'success']);
        
        if ($amount > 0 && $payment->status != 1 && $isPaid) {
            \Log::info('Cryptobot callback: processing payment', [  
                'payment_id' => $payment->id,
                'amount' => $amount,
                'status' => $status,
                'current_payment_status' => $payment->status,
                'is_paid' => $isPaid
            ]);

            // Обновляем сумму, если она отличается
            if ((float)$amount != (float)$payment->sum) {
                \Log::warning('Cryptobot callback: amount mismatch', [
                    'payment_id' => $payment->id,
                    'expected' => $payment->sum,
                    'received' => $amount
                ]);
                $payment->sum = (float)$amount;
                $payment->save();
            }

            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $balanceBefore = $user->balance;
            
            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);
            
            // Обновляем пользователя, чтобы получить актуальный баланс
            $user->refresh();

            Action::create([
                'user_id' => $user->id,
                'action' => 'Пополнение через Cryptobot',
                'balanceBefore' => $balanceBefore,
                'balanceAfter' => round($user->balance, 2)
            ]);

            NotificationsServiceFacade::sendDepositConfirmation($payment);

            $payment->status = PaymentStatusEnum::SUCCESS;
            $payment->save();

            \Log::info('Cryptobot callback: payment processed successfully', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'balance_before' => $balanceBefore,
                'balance_after' => $user->balance,
                'increment_sum' => $incrementSum,
                'payment_status_after' => $payment->status
            ]);
        } else {
            \Log::warning('Cryptobot callback: payment not processed', [
                'payment_id' => $payment->id,
                'amount' => $amount,
                'status' => $status,
                'payment_status' => $payment->status,
                'condition_check' => [
                    'amount > 0' => $amount > 0,
                    'payment->status != 1' => $payment->status != 1,
                    'is_paid' => $isPaid,
                    'status_lowercase' => strtolower($status ?? '')
                ]
            ]);
        }
        
        return ['ok'];
    }

    public function createGotham(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = $request->amount;

        if ($request->system == 'gotham_sbp') {
            if ($amount < 100) {
                return [
                    'error' => true,
                    'message' => 'Минимальная сумма пополнения для выбранного метода 100р.'
                ];
            }
            if ($amount > 999) {
                return [
                    'error' => true,
                    'message' => 'Максималная сумма пополнения для выбранного метода 999р.'
                ];
            }
        } else {
            if ($amount < 500) {
                return [
                    'error' => true,
                    'message' => 'Минимальная сумма пополнения для выбранного метода 500р.'
                ];
            }
        }


        $amount = $request->amount;

        $code = $request->code;
        $wager = 3;
        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => intval($amount),
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);


        $username = 'StimuleWin';
        $token = 'S7zaVEASfDb6Ph7JWeUzGrsgF5mMttra';


        // URL API
        $apiUrl = 'https://gotham-trade.com/api/v1/make_order/pay_in';

        if ($request->system == 'gotham_sbp') {
            $traffic_type = 'sbp';
        } else {
            $traffic_type = 'nspk';
        }

        // Подготовка тела запроса
        $paymentData = [
            'amount' => intval($amount),
            'currency' => "rub",
            'traffic_type' => $traffic_type,
            'callback_url' => config('app.url') . '/callback/gotham',
            'external_id' => $order->id,
        ];


        \Log::debug($paymentData);

        // Инициализация cURL
        $response = Http::withHeaders([
            "X-Username" => $username,
            "Authorization" => "Bearer {$token}",
        ])->post($apiUrl, $paymentData);


        // Преобразование ответа в массив
        $responseData = json_decode($response, true);

        \Log::debug($responseData);

        if ($request->system == 'gotham_sbp') {
            $order->update(['merchant_meta' => $responseData['id']]);
            return $responseData;
        }

        // Возврат результата
        if (!empty($responseData['status']) && $responseData['status'] === 'opened') {
            $order->update(['merchant_meta' => $responseData['id']]);
            return response()->json([
                'success' => true,
                'url' => $responseData['card']['qrcode'], // Ссылка на форму оплаты
            ]);
        }

        return [
            'error' => true,
            'message' => 'Поменяйте сумму пополнения, добавьте 1-9 руб или поменяйте сумму на другую.'
        ];
    }

    public function callbackGotham(Request $request)
    {
        \Log::debug($request);

        $id = intval($request['external_id']);
        $payment = Payment::where('id', $id)->first();
        $amount = $request['amount'];

        if (!$payment) {
            return ['error' => 'Order not found'];
        }

        $user = User::where('id', $payment->user_id)->first();

        if ($request['status'] == 'failed') {
            $payment->status = 2;
            $payment->save();
            return ['ok'];
        }

        if ($amount > 0 && $payment->status != 1 && $request['status'] == 'completed') {
            if ($amount != $payment->sum) {
                $payment->sum = $amount;
                $payment->save();
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Пополнение через Gotham',
                'balance_before' => $user->balance - $incrementSum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }

    private function calculateSignatureP2plab(string $method, string $url, string $bodyContent, string $secret): string
    {
        $stringToSign = $method . $url . $bodyContent;

        return base64_encode(hash_hmac('sha1', $stringToSign, $secret, true));
    }

    public function createP2plab(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = $request->amount;


        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 100р.'
            ];
        }

        $amount = $request->amount;

        $code = $request->code;
        $wager = 3;
        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => intval($amount),
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);


        $apiKey = 'f0b8f8a1d3c7ba22f96bc3edd8526ff18439540a';
        $secret = 'xY0GRqzhvWDyNmTdb7j4JiztoIv26zfj7bURBrak';

        // URL API
        $apiUrl = 'https://api.p2p-lab.com/api/merchant/invoices';

        // Подготовка тела запроса
        $paymentData = [
            'type' => 'in',
            'PaymentOption' => 'SBP',
            'amount' => strval($amount),
            'currency' => "RUB",
            'notificationUrl' => config('app.url') . '/callback/p2plab',
            'notificationToken' => strval(random_int(10000, 99999)),
            'internalId' => strval($order->id),
            'userId' => strval($user->id)
        ];

        $signature = $this->calculateSignatureP2plab('POST', $apiUrl, json_encode($paymentData), $secret);


        \Log::debug($paymentData);

        // Инициализация cURL
        $response = Http::withHeaders([
            "X-Identity" => $apiKey,
            "X-Signature" => $signature,
            "Content-Type" => 'application/json',
        ])->post($apiUrl, $paymentData);


        // Преобразование ответа в массив
        $responseData = json_decode($response, true);

        \Log::debug($responseData);

        // return ['error' => true, 'message' => $responseData];

        // Возврат результата
        if (!empty($responseData['status']) && $responseData['status'] === 'new') {
            $order->update(['merchant_meta' => $responseData['id']]);
            return response()->json([
                'success' => true,
                'url' => $responseData['invoiceUrl'], // Ссылка на форму оплаты
            ]);
        }

        return [
            'error' => true,
            'message' => 'Поменяйте сумму пополнения, добавьте 1-9 руб или поменяйте сумму на другую.'
        ];
    }
   public function TestApiRoutes()
    {

        //return ['ok200'];
        $message = "Work";
		$url = file_get_contents('https://api.telegram.org/bot7158462822:AAHgt-VuXoGr-E5wXd3lqBzNTM_gWhP_V9w/sendMessage?chat_id=-4967657255&text='.$message.'');
		
        return 'Смс отправлено';
    }
    public function callbackP2plab(Request $request)
    {

	
        //return ['ok200'];
        $message = "✅ Аккаунт успешно привязан!\n\n";
        $message .= "🎉 Добро пожаловать в НАЕБ клуб!";

        $this->telegram->sendMessage('359402496', $message);

        return response()->json(['status' => 'ok']);
    }

    public function createRepay(Request $request)
    {
        $user = User::find($request->user()->id);
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }

        $amount = $request->amount;


        if ($amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения для выбранного метода 100р.'
            ];
        }

        $amount = $request->amount;

        $code = $request->code;
        $wager = 3;
        $bonus = 0;

        // if (date('D') == 'Sun' && $request->amount >= 150) {
        //     $bonus += 10;
        // }

        if (isset($code)) {
            $promo = Promocode::where('name', $code)->lockForUpdate()->first();

            if (!$promo) {
                return [
                    'error' => true,
                    'message' => 'Промокод не найден'
                ];
            }

            if ($promo->type != 'deposit') {
                return [
                    'error' => true,
                    'message' => 'Этот промокод нужно активировать во вкладке "Бонусы"'
                ];
            }

            $allUsed = PromocodeActivation::where('promo_id', $promo->id)->count('id');

            if ($allUsed >= $promo->activation) {
                return [
                    'error' => true,
                    'message' => 'Промокод закончился'
                ];
            }

            $used = PromocodeActivation::where([['promo_id', $promo->id], ['user_id', $this->user->id]])->first();

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

            PromocodeActivation::create([
                'promo_id' => $promo->id,
                'user_id' => $this->user->id
            ]);

            $bonus += $promo->sum;
            $wager = $promo->wager;
        }

        $order = Payment::create([
            "user_id" => $user->id,
            "sum" => intval($amount),
            "system" => $request->system,
            'wager' => $wager,
            'bonus' => $bonus,
        ]);

        $cassa_api = '1b7742da-d403-443c-8b38-f5f3d1b7cbaf';
        $secret_1 = 'a9c6fe51-e74c-4c0c-9475-44c6d28070a9';
        $secret_2 = '408199e7-e009-45ca-9387-60423a930fbd';
        $cassa_ID = 'ed2365f1-2219-4eff-aa09-56d4caaf1bba';

        $method_id_sbp_rub = '7c5cdc4b-7095-4e27-be80-9497faf60e00'; // Method ID SBP RUB
        $method_id_oneclick = '5668b314-c955-4d30-8713-abb9221b4d7a'; // Method ID OneClick
        $method_id_tpay = 'f93bb783-9b40-4d98-a167-0efa5acb32f0';    // Method ID TPay
        $method_id_sberpay_rub = 'da8f391a-7988-480a-9668-00857267ae2d'; // Method ID SberPay RUB

        $signature = md5($cassa_ID . ':' . $amount . ':' . $secret_1);

        if ($request->system == 'repay_spay') {
            $payment_method_id = $method_id_sberpay_rub;
        } else {
            $payment_method_id = $method_id_tpay;
        }


        // URL API
        $apiUrl = 'https://repay.cx/api/v1/private/deals/';


        // Подготовка тела запроса
        $paymentData = [
            'cassa_id' => $cassa_ID,
            'amount' => intval($amount),
            'currency' => "RUB",
            'payment_method_id' => $payment_method_id,
            'order_id' => (string)$order->id,
            'success_url' => 'https://stimule1.win/pay',
            'error_url' => 'https://stimule1.win/pay',
            'cancel_url' => 'https://stimule1.win/pay',
            'webhook_url' => 'https://stimule1.win/callback/repay',
            'signature' => $signature,
            'payload' => [
                'user_id' => (string)$user->id,
                'trusted' => false
            ]
        ];


        \Log::debug($paymentData);

        // Инициализация cURL
        $response = Http::withHeaders([
            "Content-Type" => "application/json",
        ])->post($apiUrl, $paymentData);


        // Преобразование ответа в массив
        $responseData = json_decode($response, true);


        \Log::debug($responseData);

        // Возврат результата
        if (!empty($responseData['payload']['state']) && $responseData['payload']['state'] === 'created') {
            $order->update(['merchant_meta' => $responseData['payload']['id']]);
            return response()->json([
                'success' => true,
                'url' => $responseData['payload']['payment_url'], // Ссылка на форму оплаты
            ]);
        }

        return [
            'error' => true,
            'message' => 'Поменяйте сумму пополнения, добавьте 1-9 руб или поменяйте сумму на другую.'
        ];
    }

    public function callbackRepay(Request $request)
    {
        \Log::debug($request);

        $id = intval($request['order_id']);
        $payment = Payment::where('id', $id)->first();
        $amount = $request['amount'];

        if (!$payment) {
            return ['error' => 'Order not found'];
        }

        $user = User::where('id', $payment->user_id)->first();

        if ($request['state'] == 'rejected_timeout' || $request['state'] == 'rejected_by_system') {
            $payment->status = 2;
            $payment->save();
            return ['ok'];
        }

        if ($amount > 0 && $payment->status != 1 && $request['state'] == 'completed') {
            if ($amount != $payment->sum) {
                $payment->sum = $amount;
                $payment->save();
            }
            if (!is_null($user->referral_use)) {
                $this->setReferralProfit($user->id, $payment->sum);
            }

            $incrementSum = $payment->bonus != 0
                ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
                : $payment->sum;

            $user->increment('wager', $payment->sum * 3);
            $user->increment('balance', $incrementSum);

            if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                \Cache::put('user.' . $user->id . '.historyBalance', '[]');
            }

            $hist_balance = array(
                'user_id' => $user->id,
                'type' => 'Пополнение через Repay',
                'balance_before' => $user->balance - $incrementSum,
                'balance_after' => round($user->balance, 2),
                'date' => date('d.m.Y H:i:s')
            );

            $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

            $cashe_hist_user = json_decode($cashe_hist_user);
            $cashe_hist_user[] = $hist_balance;
            $cashe_hist_user = json_encode($cashe_hist_user);
            \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);

            $payment->status = 1;
            $payment->save();
        }
        return ['ok'];
    }
}
