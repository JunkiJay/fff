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
class PaymentFailAction extends Action
{
    public function handle(Payment $payment): void
    {
        $payment->status = PaymentStatusEnum::FAILED;
        $payment->save();
    }
}