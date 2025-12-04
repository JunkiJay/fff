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
        $amount = $this->reduceAmountByBonusPercents($payment);
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

        // Проверяем сумму
        if ((float)$amount != (float)$payment->sum) {
            Log::warning('FK Wallet callback amount mismatch', [
                'payment_id' => $payment->id,
                'expected' => $payment->sum,
                'received' => $amount
            ]);
            // Обновляем сумму, если она отличается
            $payment->sum = (float)$amount;
            $payment->save();
        }

        // Обрабатываем платеж
        $user = $payment->user;
        
        if (!$user) {
            Log::error('FK Wallet callback user not found', ['payment_id' => $payment->id]);
            return new PaymentErrorResult('User not found');
        }

        $incrementSum = $payment->bonus != 0
            ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
            : $payment->sum;

        // Обновляем баланс пользователя
        $user->increment('wager', $payment->sum * 3);
        $user->increment('balance', $incrementSum);

        // Обновляем статус платежа
        $payment->status = 1;
        $payment->save();

        Log::info('FK Wallet payment processed successfully', [
            'payment_id' => $payment->id,
            'user_id' => $user->id,
            'amount' => $incrementSum
        ]);

        return new PaymentSuccessResult();
    }
}