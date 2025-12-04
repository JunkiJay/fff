<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CryptocurrencyConvertorHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Payments\Actions\Withdraws\WithdrawSendToProviderAction;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class WithdrawsController extends Controller
{
    public function index()
    {
        return view('admin.withdraws.index');
    }

    public function decline(Request $request)
    {
        $withdraw = Withdraw::query()->find($request->id);

        if (!$withdraw) {
            return [
                'error' => 'Выплата отменена пользователем'
            ];
        }

        if ($withdraw->status > 0) {
            return [
                'error' => 'Статус выплаты уже изменен ранее'
            ];
        }
        if ($request->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }

        if ($request->status == 2) {

            if ($request->returnBalance == 1) {
                $user = User::where('id', $withdraw->user_id)->lockForUpdate()->first();
                $user->balance += $withdraw->sum;
                $user->save();
            }

            $withdraw->update([
                'status' => $request->status,
                'reason' => $request->reason
            ]);
        }
    }

    public function send(Request $request)
    {
        if ($request->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }
        $id = $request->id;
        $status = $request->status;

        $withdraw = Withdraw::where('id', $id)->lockForUpdate()->first();

        if (!$withdraw) {
            return [
                'error' => true,
                'message' => 'Выплата отменена пользователем',
                'reload' => true
            ];
        }

        if ($withdraw->status > 0) {
            return [
                'error' => true,
                'message' => 'Статус выплаты уже изменен ранее',
                'reload' => true
            ];
        }

        $withdraw->update([
            'status' => 3
        ]);

        return [
            'message' => 'Выплата отправлена',
            'status' => $withdraw->status
        ];
    }

    public function payout(Request $request)
    {
        if ($request->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }

        $id = (int) $request->id;

        return DB::transaction(static function () use($id) {
            $result = WithdrawSendToProviderAction::run($id);

            return [
                'message' => 'Выплата отправлена',
                'status' => $result->status
            ];
        });
    }

    private function sendSbpPayoutRequest(array $postData)
    {
        $jsonData = json_encode($postData);

        $headers = [
            'Authorization: Bearer 4fd45h43hj6f5h6v1c3c',
            'Content-Type: application/json'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://business.cixsdpxj.info/api/v1/payouts',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    public function acceptParadiseSbpPayout(Request $request)
    {
        if ($request->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }

        $id = $request->id;

        DB::beginTransaction();

        try {
            $withdraw = Withdraw::find($id);

            if (!$withdraw) {
                return $this->errorResponse('Выплата отменена пользователем');
            }

            if ($withdraw->status > 0) {
                return $this->errorResponse('Статус выплаты уже изменен ранее');
            }


            if ($withdraw->sumWithCom < 2000) {
                return $this->errorResponse('Минимальная сумма вывода 2000 ₽');
            }


            $system = $withdraw->system;

            if (!in_array($system, ['tinkoff', 'sberbank', 'alfabank'])) {
                return $this->errorResponse('Этот метод вывода предназначень только для Т-банк, Сбербанк, Алфабанк');
            }

            $wallet = $this->antiFormatWallet($withdraw->wallet);

            $postData = [];
            $sbp_bank = '';

            if (in_array($system, ['tinkoff', 'sberbank', 'alfabank'])) {
                if ($system == 'tinkoff') {
                    $sbp_bank = "Т-Банк";
                } elseif ($system == 'sberbank') {
                    $sbp_bank = "Сбербанк";
                } elseif ($system == 'alfabank') {
                    $sbp_bank = "АЛЬФА-БАНК";
                }
            } else {
                return $this->errorResponse('Неизвестная платёжная система');
            }

            $totalAmount = intval($withdraw->sumWithCom * 100);

            $postData = [
                "amount" => $totalAmount,
                "phone" => $wallet,
                "bank" => $sbp_bank,
                "metadata" => [
                    "orderNumber" => strval($withdraw->id),
                ]
            ];

            Log::info('Withdraw postData', $postData);

            $response = $this->sendParadiseSbpPayoutRequest($postData);

            Log::info('Withdraw response ', [$response]);

            if (isset($response['status']) && $response['status'] == 'waiting') {
                $withdraw->update(['status' => 3]);
            } else {
                return [
                    'error' => true,
                    'message' => $response,
                    'reload' => true
                ];
            }

            DB::commit();

            return [
                'message' => 'Выплата отправлена',
                'status' => $withdraw->status
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Withdraw error', ['error' => $e->getMessage()]);
            return $this->errorResponse('Произошла ошибка при обработке выплаты');
        }
    }


    private function sendParadiseSbpPayoutRequest(array $postData)
    {

        $jsonData = json_encode($postData);

        $shop_id = 8;
        $secret = 'prod_FdxJk1vWwn6whLOc4sQ3GF0g';

        $url = 'https://api.p2p-paradise.info/payouts';

        $response = Http::withHeaders([
            "merchant-id" => $shop_id,
            "merchant-secret-key" => $secret,
            "Content-Type" => "application/json",
        ])->post($url, $postData);

        $result = $response->json();

        Log::info('Logs ответ от Paradise After', [
            'body' => $postData,
            'headers' => [
                "merchant-id" => $shop_id,
                "merchant-secret-key" => $secret,
            ],
            'url' => $url,
            'response' => $result,
        ]);

        return $result;
    }


    public function acceptCryptobotPayout(Request $request)
    {
        if ($request->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }

        try {
            $result = WithdrawSendToProviderAction::run((int)  $request->id);

            return [
                'message' => 'Выплата отправлена',
                'status' => $result->status
            ];
        } catch (ValidationException $e) {
            return $this->errorResponse(Arr::first($e->errors()));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Withdraw error', ['error' => $e->getMessage()]);
            return $this->errorResponse('Произошла ошибка при обработке выплаты');
        }
    }



    public function acceptFKPayout(Request $request)
    {
        if ($request->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }

        try {
            $withdraw = WithdrawSendToProviderAction::run((int)  $request->id);

            return [
                'message' => $withdraw->message ?? 'Выплата отправлена',
                'status' => $withdraw->status
            ];
        } catch (ValidationException $e) {
            return $this->errorResponse(Arr::first($e->errors()));
        } catch (\DomainException $e) {
            return $this->errorResponse($e->getMessage());
        } catch (\Exception $e) {
            Log::error('Withdraw error', ['error' => $e->getMessage()]);
            return $this->errorResponse('Произошла ошибка при обработке выплаты');
        }
    }

    private function getUserIdByUsername($username)
    {

        $url = "http://91.84.105.200/get_id";

        $data = [
            'username' => $username
        ];

        $result = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $data);

        $response = json_decode($result, true);

        if ($response['id']) {
            return $response['id'];
        } else {
            return [
                'error' => true,
                'message' => 'Не смогли найти ваш телеграм ид, обратитесь в техподдержку! '
            ];
        }
    }

    // Вспомогательные методы

    private function antiFormatWallet($wallet)
    {
        return trim($wallet, [' ', '+']);
    }

    private function errorResponse($message)
    {
        return [
            'error' => true,
            'message' => $message,
            'reload' => true
        ];
    }

    private function formatWallet($wallet)
    {
        // Проверка и добавление символа "+" перед кошельком, если его нет
        if (isset($wallet[0]) && $wallet[0] == '+' || $wallet[0] != 7) {
            return $wallet;
        }

        return '+' . $wallet;
    }

    private function sendPayoutRequest(array $postData)
    {
        $jsonData = json_encode($postData);

        $headers = [
            'Authorization: Bearer ' . '830230f47120c6de6718133ab8679358c71f593376be68ed',
            'Content-Type: application/json'
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'http://onepayments.tech/api/v1/external_processing/payments/withdrawals',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }



    public function waitingSend(Request $request)
    {
        if ($request->user()->admin_role == 'moder') {
            return [
                'error' => true,
                'message' => 'Ваша роль не позволяет совершить это действие',
                'reload' => true
            ];
        }
        $id = $request->id;
        $status = $request->status;

        $withdraw = Withdraw::where('id', $id)->lockForUpdate()->first();

        if (!$withdraw) {
            return [
                'error' => true,
                'message' => 'Выплата отменена пользователем',
                'reload' => true
            ];
        }

        if ($withdraw->status != 3) {
            return [
                'error' => true,
                'message' => 'Статус выплаты уже изменен ранее',
                'reload' => true
            ];
        }

        $withdraw->update([
            'status' => 1
        ]);

        return [
            'message' => 'Выплата завершена',
            'status' => $withdraw->status
        ];
    }

    public function getById(Request $request)
    {
        return Withdraw::where('withdraws.id', $request->id)
            ->join('users', 'users.id', '=', 'withdraws.user_id')
            ->where('users.is_youtuber', '<', 1)
            ->select('users.username as username', 'withdraws.*')
            ->first();
    }
}
