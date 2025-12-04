<?php

declare(strict_types=1);

namespace App\Services\Payments\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

readonly class PaymentShowC2CResult implements Arrayable
{
    public function __construct(
        public string $action,
        public string $card,
        public string $name,
        public string $bank,
        public int $amount,
        public ?string $orderId = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'card' => $this->card,
            'name' => $this->name,
            'bank' => $this->bank,
            'amount' => $this->amount,
        ];
    }
}