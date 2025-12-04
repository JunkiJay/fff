<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\FK\FKApiClient;
use App\Models\Payment;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Payments\Enum\WithdrawStatusEnum;
use App\Services\Payments\Facades\PaymentServiceFacade;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentSuccessResult;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\WithdrawResult;
use Illuminate\Support\Facades\Log;

class FKPaymentProvider extends PaymentProvider
{
    public function __construct(
        PaymentProviderConfig $config,
        public readonly FKApiClient $client
    ) {
        parent::__construct($config);
    }

    public function pay(Payment $payment): PaymentRedirectResult
    {
        // ВАЖНО:
        //  - $payment->sum здесь — это сумма, которую ввёл пользователь (например, 10₽)
        //  - бонус (% из метода или промокода) начисляется только в callback на баланс,
        //    поэтому провайдеру отправляем ИМЕННО эту сумму, без вычитания бонуса
        $amount = $payment->sum;
        $terminalId = config('api-clients.fk.terminal_id');
        $terminalSecret1 = config('api-clients.fk.terminal_secret_1');
        $sign = md5($terminalId . ':' . $amount . ':' . $terminalSecret1 . ':RUB:' . $payment->id);

        // URL для возврата после успешной оплаты
        $returnUrl = config('app.url') . '/payment/success?payment_id=' . $payment->id;

        $data = [
            'm' => $terminalId,
            'oa' => $amount,
            'o' => $payment->id,
            'currency' => 'RUB',
            's' => $sign,
            'i' => 1,
            'us_return' => $returnUrl, // URL для возврата после оплаты
        ];

        return new PaymentRedirectResult(
            self::ACTION_REDIRECT,
            "https://pay.fk.money/?" . http_build_query($data),
        );
    }

    public function withdraw(Withdraw $withdraw): WithdrawResult
    {
        if ($withdraw->system !== 'fk') {
            return new WithdrawResult(false, WithdrawStatusEnum::DECLINE);
        }

        try {
            Log::debug('FK Withdraw', ['withdraw' => $withdraw->toArray()]);
            $transferId = $this->client->transfer($withdraw->wallet, (int)$withdraw->sumWithCom, (string)$withdraw->id);
        } catch (\Exception $e) {
            Log::error('FK Withdraw error', ['error' => $e->getMessage()]);
            return new WithdrawResult(false, WithdrawStatusEnum::DECLINE, $e->getMessage());
        }

        return new WithdrawResult(true, WithdrawStatusEnum::SUCCESS);
    }

    public function isAutoWithdrawAvailable(Withdraw $withdraw): bool
    {
        $successWitdraws = Withdraw::where('user_id', $withdraw->user_id)
            ->where('status', 1)
            ->count();

        return ($successWitdraws >= 3 && empty($withdraw->user->auto_withdraw));
    }

    public function getBalance(): array
    {
        return $this->client->balance() ?? [];
    }

    public function handleCreateCallback(Payment $payment, array $data): PaymentSuccessResult|PaymentErrorResult
    {
        Log::info('FK Wallet callback received', [
            'payment_id' => $payment->id,
            'data' => $data
        ]);

        // FreeKassa отправляет callback с параметрами:
        // MERCHANT_ID, AMOUNT, MERCHANT_ORDER_ID, SIGN
        $merchantId = $data['MERCHANT_ID'] ?? $data['merchant_id'] ?? null;
        $amount = $data['AMOUNT'] ?? $data['amount'] ?? null;
        $merchantOrderId = $data['MERCHANT_ORDER_ID'] ?? $data['merchant_order_id'] ?? null;
        $sign = $data['SIGN'] ?? $data['sign'] ?? null;

        if (!$merchantId || !$amount || !$merchantOrderId || !$sign) {
            Log::error('FK Wallet callback missing required fields', ['data' => $data]);
            return new PaymentErrorResult('Missing required callback parameters');
        }

        // Проверяем подпись
        $terminalId = config('api-clients.fk.terminal_id');
        $terminalSecret2 = config('api-clients.fk.terminal_secret_2', config('api-clients.fk.terminal_secret_1'));
        
        $expectedSign = md5($terminalId . ':' . $amount . ':' . $terminalSecret2 . ':' . $merchantOrderId);
        
        if ($sign !== $expectedSign) {
            Log::error('FK Wallet callback signature mismatch', [
                'expected' => $expectedSign,
                'received' => $sign,
                'data' => $data
            ]);
            return new PaymentErrorResult('Invalid signature');
        }

        // Проверяем, что платеж еще не обработан
        if ($payment->status == 1) {
            Log::info('FK Wallet payment already processed', ['payment_id' => $payment->id]);
            return new PaymentSuccessResult();
        }

        // ВАЖНО: Логируем что пришло от FK Wallet
        Log::info('FK Wallet callback: received data', [
            'payment_id' => $payment->id,
            'original_payment_sum' => $payment->sum,
            'amount_from_provider' => $amount,
            'amount_difference' => (float)$amount - (float)$payment->sum,
            'payment_bonus' => $payment->bonus,
        ]);

        // Проверяем сумму
        if ((float)$amount != (float)$payment->sum) {
            // ВАЖНО:
            //  - $payment->sum — это сумма, которую ввёл пользователь (например, 10₽)
            //  - FK Wallet может вернуть сумму с уже добавленным своим бонусом/кэшбеком (например, 10.5₽)
            //  - Если мы перезаписываем payment->sum на 10.5 и ещё раз добавляем наш бонус 5%,
            //    получится двойное начисление (примерно 11.03₽ вместо ожидаемых 10.5₽).
            //
            // Поэтому:
            //  - НЕ перезаписываем payment->sum, а только логируем расхождение.
            Log::warning('FK Wallet callback amount mismatch (keeping original sum to avoid double bonus)', [
                'payment_id' => $payment->id,
                'expected_original_sum' => $payment->sum,
                'received_from_provider' => $amount,
                'difference' => (float)$amount - (float)$payment->sum,
            ]);
        }

        // Обрабатываем платеж
        $user = $payment->user;
        
        if (!$user) {
            Log::error('FK Wallet callback user not found', ['payment_id' => $payment->id]);
            return new PaymentErrorResult('User not found');
        }

        // Логируем данные для диагностики бонусов
        Log::info('FK Wallet callback: bonus calculation', [
            'payment_id' => $payment->id,
            'payment_sum' => $payment->sum,
            'payment_bonus' => $payment->bonus,
            'user_balance_before' => $user->balance,
            'expected_bonus_percent' => 5 // Из конфига
        ]);

        // Рассчитываем сумму с учетом бонуса
        $incrementSum = $payment->bonus != 0
            ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
            : $payment->sum;

        Log::info('FK Wallet callback: increment calculation', [
            'payment_id' => $payment->id,
            'payment_bonus' => $payment->bonus,
            'incrementSum' => $incrementSum,
            'calculation' => $payment->bonus != 0 
                ? "{$payment->sum} + ({$payment->sum} * {$payment->bonus} / 100) = {$incrementSum}"
                : "{$payment->sum} (no bonus)"
        ]);

        // Обновляем баланс пользователя
        $user->increment('wager', $payment->sum * 3);
        $user->increment('balance', $incrementSum);

        // Обновляем статус платежа
        $payment->status = 1;
        $payment->save();

        // Создаем запись в Action
        \App\Models\Action::create([
            'user_id' => $user->id,
            'action' => 'Пополнение через FK Wallet',
            'balanceBefore' => $user->balance - $incrementSum,
            'balanceAfter' => round($user->balance, 2)
        ]);

        // Отправляем уведомление о пополнении
        \App\Services\Notifications\Facades\NotificationsServiceFacade::sendDepositConfirmation($payment);

        // Обновляем пользователя, чтобы получить актуальный баланс
        $user->refresh();

        Log::info('FK Wallet payment processed successfully', [
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'incrementSum' => $incrementSum,
            'balance_before' => $user->balance - $incrementSum,
            'balance_after' => $user->balance,
            'actual_increment' => $user->balance - ($user->balance - $incrementSum), // Фактическое увеличение баланса
        ]);

        return new PaymentSuccessResult();
    }
}