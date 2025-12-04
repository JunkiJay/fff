<?php

declare(strict_types=1);

namespace App\Services\Payments\Actions;

use App\Enums\Payments\PaymentStatusEnum;
use App\Models\Payment;
use App\Services\Notifications\Facades\NotificationsServiceFacade;
use App\Services\Users\Actions\UserSetReferralProfitAction;
use FKS\Actions\Action;

/**
 * @method static void run(Payment $payment)
 */
class PaymentCompleteAction extends Action
{
    public function handle(Payment $payment): void
    {
        $user = $payment->user;

        UserSetReferralProfitAction::run($user, $payment->sum);

        $incrementSum = $payment->bonus != 0
            ? $payment->sum + (($payment->sum * $payment->bonus) / 100)
            : $payment->sum;

        $user->increment('wager', $payment->sum * 3);
        $user->increment('balance', $incrementSum);

        $payment->status = PaymentStatusEnum::SUCCESS;
        $payment->save();

        \App\Models\Action::create([
            'user_id' => $user->id,
            'action' => 'Пополнение через ' . $payment->system,
            'balanceBefore' => $user->balance - $incrementSum,
            'balanceAfter' => round($user->balance, 2)
        ]);

        NotificationsServiceFacade::sendDepositConfirmation($payment);
    }
}