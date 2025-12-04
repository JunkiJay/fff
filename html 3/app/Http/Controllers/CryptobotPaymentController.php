<?php

namespace App\Http\Controllers;

use App\Enums\Payments\PaymentStatusEnum;
use App\Models\Payment;
use App\Models\Promocode;
use App\Models\PromocodeActivation;
use App\Models\Withdraw;
use App\Services\CryptobotPayment\CryptobotPaymentService;
use App\Services\Notifications\Facades\NotificationsServiceFacade;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CryptobotPaymentController extends Controller
{
    private $paymentService;

    public function __construct(CryptobotPaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
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


    public function createPayment(Request $request)
    {
        $user = $request->user();
        if ($user->ban) {
            return ["error" => "Ваш аккаунт заблокирован"];
        }


        if ($request->amount < 100) {
            return [
                'error' => true,
                'message' => 'Минимальная сумма пополнения 100 руб'
            ];
        }

        $amount = $this->convertRubToUsdt($request, $request->amount);

        $code = $request->code;
        $wager = 3;

        $bonus = 0;

        if (date('D') == 'Sun' && $request->amount >= 150) {
            $bonus += 10;
        }

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

        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric',
                'system' => 'required|string'
            ]);

            if ($validator->fails()) {
                return [
                    'error' => true,
                    'message' => $validator->errors()->first()
                ];
            }


            $payment = Payment::create([
                'user_id' => $user->id,
                'sum' => $amount,
                'bonus' => 1,
                'wager' => 3,
                'system' => $request->system,
                'status' => 0
            ]);

            $paymentData = [
                'currency_type' => "crypto",
                'asset' => "USDT", // BTC, TON
                'fiat' => "USD",
                'accepted_assets' => "USDT",
                'amount' => $amount,
                'comment' => $payment->id
            ];

            $response = $this->paymentService->createPayment($paymentData);

            return $response;
        } catch (Exception $e) {
            Log::error('Payment creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'amount' => $amount ?? null
            ]);

            return [
                'error' => true,
                'message' => 'Payment creation failed: ' . $e->getMessage()
            ];
        }
    }


    public function handleWebhook(Request $request)
    {
        try {
            Log::info('Payment webhook received', $request->all());

            $payment = Payment::where('id', $request->merchant_id)->firstOrFail();


            $newStatus = $request->status === 'successed' ? PaymentStatusEnum::SUCCESS : PaymentStatusEnum::FAILED;

            $payment->update([
                'status' => $newStatus
            ]);

            // Если платеж успешный, можно добавить доп. логику
            if ($newStatus === 1) {
                NotificationsServiceFacade::sendDepositConfirmation($payment);
            }

            return response()->json(['status' => 'ok']);
        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function createWithdraw(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric',
                'system' => 'required|string'
            ]);

            if ($validator->fails()) {
                return [
                    'error' => true,
                    'message' => $validator->errors()->first()
                ];
            }

            $amount = $request->amount;
            $user = $request->user();


            $withdraw = Withdraw::create([
                'user_id' => $user->id,
                'sum' => $amount,
                'sumWithCom' => $amount,
                'wallet' => $request->system,
                'system' => $request->system,
                'status' => 0
            ]);

            $paymentData = [
                'asset' => "USDT", // BTC, TON
                'amount' => $amount
            ];

            $response = $this->paymentService->createWithdraw($paymentData);

            return $response;
        } catch (Exception $e) {
            Log::error('Withdraw creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'amount' => $amount ?? null
            ]);

            return [
                'error' => true,
                'message' => 'Withdraw creation failed: ' . $e->getMessage()
            ];
        }
    }
}
