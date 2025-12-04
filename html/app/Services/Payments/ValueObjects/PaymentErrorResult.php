<?php

declare(strict_types=1);

namespace App\Services\Payments\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

readonly class PaymentErrorResult implements Arrayable
{
    public function __construct(public string|array $error) {}

    public function toArray(): array
    {
        if (is_array($this->error)) {
            return ['errors' => $this->error];
        }

        return [
            'error' => $this->error,
        ];
    }
}