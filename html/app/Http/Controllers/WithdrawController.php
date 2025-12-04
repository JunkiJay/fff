<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Payments\Actions\Withdraws\WithdrawAction;
use App\Services\Payments\DTO\CreateWithdrawDTO;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\PaymentProvidersEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    public function create(Request $request): array
    {
        $withdraw = WithdrawAction::run(
            new CreateWithdrawDTO(
                $request->sum,
                $request->wallet,
                PaymentProvidersEnum::tryFrom($request->provider),
                PaymentMethodEnum::tryFrom($request->get('method')),
                auth()->user(),
                $request->get('variant')
            )
        );

        return [
            'withdraw' => $withdraw->toArray(),
            'balance' => auth()->user()->refresh()->balance,
        ];
    }

    public function decline(Request $request): array
    {
        DB::beginTransaction();

        try {
            $withdraw = Withdraw::where('id', $request->id)->lockForUpdate()->first();
            
            if (!$withdraw) {
                return [
                    'error' => true,
                    'message' => 'Выплата не найдена'
                ];
            }

            if ($withdraw->status) {
                return [
                    'error' => true,
                    'message' => 'Статус выплаты уже изменен'
                ];
            }

            if ($withdraw->user_id != $this->user->id) {
                return [
                    'error' => true,
                    'message' => 'Эта выплата не принадлежит вам'
                ];
            }

            $withdraw->status = 2;
            $withdraw->reason = 'Выплата отменена пользователем';
            $withdraw->save();

            $amount = $withdraw->sum;

            $this->user->increment('balance', $amount);

            Action::create([
                'user_id' => $this->user->id,
                'action' => 'Отмена вывода',
                'balanceBefore' => round($this->user->balance - $amount, 2),
                'balanceAfter' => round($this->user->balance, 2),
            ]);

            DB::commit();

            return [
                'balance' => $this->user->balance
            ];
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error declining withdraw: ' . $e->getMessage());
            
            return [
                'error' => true,
                'message' => 'Произошла ошибка при отмене выплаты'
            ];
        }
    }

    /**
     * Handle FKWallet webhook requests
     *
     * @param Request $request
     * @return string Response message
     */
    public function fkwalletHandle(Request $request): string
    {
        if (!in_array($this->getIp(), ['136.243.38.149', '136.243.38.150', '136.243.38.151'])) {
            \Log::warning('Unauthorized FKWallet webhook access attempt from IP: ' . $this->getIp());
            return 'hacking attempt!';
        }

        DB::beginTransaction();

        try {
            $withdraw = Withdraw::find($request->user_order_id);

            if (!$withdraw) {
                \Log::warning('FKWallet webhook: withdraw not found for ID: ' . $request->user_order_id);
                return 'withdraw not found!';
            }

            $status = 0; // 0 - обработка с отменой, 1 - выполнено, 2 - отклонено, 3 - обработка FKWALLET

            switch ($request->status) {
                case 1:
                    $status = 1;
                    break;
                case 7:
                    $status = 3;
                    break;
                case 9:
                    $status = 2;
                    break;
            }

            if ($request->status == 9) {
                $user = User::find($withdraw->user_id);
                if ($user) {
                    $user->increment('balance', $withdraw->sum);
                    $withdraw->reason = 'Отказ банка. Смените реквизиты или поменяйте метод вывода.';
                } else {
                    \Log::error('FKWallet webhook: user not found for withdraw ID: ' . $withdraw->id);
                }
            }

            $withdraw->status = $status;
            $withdraw->save();

            DB::commit();
            
            \Log::info('FKWallet webhook processed successfully for withdraw ID: ' . $withdraw->id);
			

            return 'YES';
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error processing FKWallet webhook: ' . $e->getMessage());
            return 'ERROR';
        }
    }

    /**
     * Handle withdrawal callback from payment provider
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request): \Illuminate\Http\JsonResponse
    {
        // Логируем все данные для отладки
        \Log::info('withdraws callback ', $request->all());

        try {
            // Валидация входных данных
            $validated = $request->validate([
                'data.id' => 'required|string',
                'data.type' => 'required|string|in:withdrawal',
                'data.attributes.uuid' => 'required|string',
                'data.attributes.external_order_id' => 'nullable|string',
                'data.attributes.payment_status' => 'required|string|in:processer_search,transferring,confirming,completed,cancelled'
            ]);

            $externalOrderId = $validated['data']['attributes']['external_order_id'];
            $paymentStatus = $validated['data']['attributes']['payment_status'];

            // Проверяем, существует ли запись для данного id
            $withdraw = Withdraw::where('id', $externalOrderId)->first();

            if (!$withdraw) {
                \Log::error("Withdraw record not found for id: {$externalOrderId}, ip:" . $request->ip());
                return response()->json([
                    'error' => true,
                    'message' => 'Запись вывода средств не найдена'
                ], 404);
            }

            // Определение статуса выплаты
            $statusMapping = [
                'processer_search' => 0,
                'transferring' => 3,
                'confirming' => 3,
                'completed' => 1,
                'cancelled' => 2,
            ];

            // Получаем статус из маппинга или присваиваем, если статус неизвестен
            $statusValue = $statusMapping[$paymentStatus] ?? 4;

            if ($withdraw->status == 2) {
                return response()->json([
                    'message' => 'Статус обновлен ранее',
                    'withdraw' => $withdraw
                ]);
            }

            // Обновляем запись транзакции
            $withdraw->update(['status' => $statusValue]);

            if ($statusValue == 2) {
                $user = User::find($withdraw->user_id);
                if ($user) {
                    $withdraw->update(['reason' => 'Отказ банка. Смените реквизиты или поменяйте метод вывода.']);
                    $user->increment('balance', $withdraw->sum);

                    if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
                        \Cache::put('user.' . $user->id . '.historyBalance', '[]');
                    }

                    $hist_balance = [
                        'user_id' => $user->id,
                        'type' => 'Отмена вывода (отказ банка)',
                        'balance_before' => round($user->balance + $withdraw->sum, 2),
                        'balance_after' => round($user->balance, 2),
                        'date' => date('d.m.Y H:i:s')
                    ];

                    $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

                    $cashe_hist_user = json_decode($cashe_hist_user);
                    if ($cashe_hist_user === null) {
                        $cashe_hist_user = [];
                    }
                    $cashe_hist_user[] = $hist_balance;
                    $cashe_hist_user = json_encode($cashe_hist_user);
                    \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);
                } else {
                    \Log::error("User not found for withdraw ID: {$withdraw->id}");
                }
            }

            // Логируем обновление статуса
            \Log::info("Withdraw ID {$externalOrderId} updated to status: {$statusValue}");

            return response()->json([
                'message' => 'Статус обновлен',
                'withdraw' => $withdraw
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Validation error in withdraw callback: " . json_encode($e->errors()));
            return response()->json([
                'error' => true,
                'message' => 'Ошибка валидации данных',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Логируем ошибку при обновлении
            \Log::error("Error processing withdraw callback: " . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Ошибка при обновлении статуса'
            ], 500);
        }
    }

    /**
     * Handle SpinPay webhook callback
     *
     * @param Request $request
     * @return array<string, string>|string
     */
    public function callbackSpinPay(Request $request): array|string
    {
        \Log::info('withdraws SpinPay callback ', $request->all());

        if (!in_array($this->getIp(), ['35.159.146.42'])) {
            \Log::warning('Unauthorized SpinPay webhook access attempt from IP: ' . $this->getIp());
            return 'wrong ip';
        }

        try {
            $id = $request['orderNumber'] ?? null;
            if (!$id) {
                \Log::error('SpinPay callback: missing orderNumber');
                return ['error' => 'Missing orderNumber'];
            }

            $withdraw = Withdraw::where('id', $id)->first();
            if (!$withdraw) {
                \Log::error('SpinPay callback: withdraw not found for ID: ' . $id);
                return ['error' => 'Order not found'];
            }

            $amount = isset($request['amount']) ? $request['amount'] / 100 : 0;
            
            $user = User::where('id', $withdraw->user_id)->first();
            if (!$user) {
                \Log::error('SpinPay callback: user not found for withdraw ID: ' . $withdraw->id);
                return ['error' => 'User not found'];
            }

            $status = $request['status'] ?? '';

            if ($status == 'declined' || $status == 'expired') {
                $withdraw->status = 2;
                $withdraw->update(['reason' => 'Отказ банка. Смените реквизиты или поменяйте метод вывода.']);
                $user->increment('balance', $amount);
                $withdraw->save();

                $this->updateUserHistoryBalance($user, 'Отмена вывода (отказ банка)', $withdraw->sum);
                
                \Log::info('SpinPay callback: withdraw declined/expired for ID: ' . $withdraw->id);
                return ['ok'];
            }

            if ($amount > 0 && $withdraw->status != 1 && $status == 'approved') {
                $this->updateUserHistoryBalance($user, 'Вывод через SpinPay', $amount);
                
                $withdraw->status = 1;
                $withdraw->save();
                
                \Log::info('SpinPay callback: withdraw approved for ID: ' . $withdraw->id);
            }
            
            return ['ok'];
        } catch (\Exception $e) {
            \Log::error('Error processing SpinPay callback: ' . $e->getMessage());
            return ['error' => 'Internal server error'];
        }
    }
    
    /**
     * Update user history balance in cache
     *
     * @param User $user
     * @param string $type
     * @param float $amount
     * @return void
     */
    private function updateUserHistoryBalance(User $user, string $type, float $amount): void
    {
        if (!(\Cache::has('user.' . $user->id . '.historyBalance'))) {
            \Cache::put('user.' . $user->id . '.historyBalance', '[]');
        }

        $hist_balance = [
            'user_id' => $user->id,
            'type' => $type,
            'balance_before' => round($user->balance + $amount, 2),
            'balance_after' => round($user->balance, 2),
            'date' => date('d.m.Y H:i:s')
        ];

        $cashe_hist_user = \Cache::get('user.' . $user->id . '.historyBalance');

        $cashe_hist_user = json_decode($cashe_hist_user);
        if ($cashe_hist_user === null) {
            $cashe_hist_user = [];
        }
        $cashe_hist_user[] = $hist_balance;
        $cashe_hist_user = json_encode($cashe_hist_user);
        \Cache::put('user.' . $user->id . '.historyBalance', $cashe_hist_user);
    }
}
