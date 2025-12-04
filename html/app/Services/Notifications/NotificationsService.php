<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Events\DepositCinfirmationEvent;
use App\Models\Payment;

class NotificationsService
{
    public function sendDepositConfirmation(Payment $payment): void
    {
        DepositCinfirmationEvent::dispatch($payment);
    }
}