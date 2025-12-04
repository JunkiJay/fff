<?php

declare(strict_types=1);

namespace App\Services\Payments\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

readonly class PaymentRedirectResult implements Arrayable
{
    public function __construct(
        public string $action,
        public string $url,
        public ?string $orderId = null
    ) {
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'url' => $this->url,
        ];
    }
}