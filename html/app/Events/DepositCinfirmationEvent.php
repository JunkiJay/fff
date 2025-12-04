<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DepositCinfirmationEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @var Payment
     */
    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('depositConfirmation');
    }

    public function broadcastWith(): array
    {
        return [
            'payment_id' => $this->payment->id,
            'user_id' => $this->payment->user_id,
            'amount' => $this->payment->sum,
        ];
    }

    public function broadcastAs(): string
    {
        return 'deposit-confirmation';
    }
}