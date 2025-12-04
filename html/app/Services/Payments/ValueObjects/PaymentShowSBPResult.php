<?php

declare(strict_types=1);

namespace App\Services\Payments\ValueObjects;

use FKS\Serializer\SerializableObject;

class PaymentShowSBPResult extends SerializableObject
{
    public function __construct(
        public string $action,
        public string $phone,
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
            'phone' => $this->phone,
            'name' => $this->name,
            'bank' => $this->bank,
            'amount' => $this->amount,
        ];
    }
}