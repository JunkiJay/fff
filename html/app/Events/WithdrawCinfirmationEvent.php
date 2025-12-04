<?php

namespace App\Events;

use App\Models\Withdraw;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WithdrawCinfirmationEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @var Withdraw
     */
    public $withdraw;

    public function __construct(Withdraw $withdraw)
    {
        $this->withdraw = $withdraw;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('withdrawConfirmation');
    }

    public function broadcastWith(): array
    {
        return [
            'withdraw_id' => $this->withdraw->id,
            'user_id' => $this->withdraw->user_id,
            'amount' => $this->withdraw->sum,
        ];
    }

    public function broadcastAs(): string
    {
        return 'withdraw-confirmation';
    }
}