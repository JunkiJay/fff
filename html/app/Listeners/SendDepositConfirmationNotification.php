<?php

namespace App\Listeners;

use App\Events\DepositCinfirmationEvent;
use App\Models\User;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Facades\Log;

class SendDepositConfirmationNotification
{
    protected $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\DepositCinfirmationEvent  $event
     * @return void
     */
    public function handle(DepositCinfirmationEvent $event)
    {
        $payment = $event->payment;
        $user = $payment->user;

        if (!$user) {
            Log::warning('SendDepositConfirmationNotification: User not found', [
                'payment_id' => $payment->id,
                'user_id' => $payment->user_id
            ]);
            return;
        }

        // Отправляем уведомление в Telegram, если пользователь привязал Telegram
        if ($user->tg_id) {
            try {
                $message = "✅ <b>Пополнение баланса успешно!</b>\n\n";
                $message .= "💰 Сумма: <b>{$payment->sum} RUB</b>\n";
                $message .= "💳 Система: <b>{$payment->system}</b>\n";
                $message .= "📊 Новый баланс: <b>{$user->balance} RUB</b>\n";
                $message .= "🆔 ID платежа: <code>{$payment->id}</code>";

                $result = $this->telegram->sendMessage($user->tg_id, $message);

                if ($result && isset($result['ok']) && $result['ok']) {
                    Log::info('Deposit confirmation notification sent', [
                        'payment_id' => $payment->id,
                        'user_id' => $user->id,
                        'tg_id' => $user->tg_id
                    ]);
                } else {
                    Log::warning('Failed to send deposit confirmation notification', [
                        'payment_id' => $payment->id,
                        'user_id' => $user->id,
                        'tg_id' => $user->tg_id,
                        'telegram_response' => $result
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error sending deposit confirmation notification', [
                    'payment_id' => $payment->id,
                    'user_id' => $user->id,
                    'tg_id' => $user->tg_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        } else {
            Log::debug('User has no Telegram ID, skipping notification', [
                'payment_id' => $payment->id,
                'user_id' => $user->id
            ]);
        }
    }
}

