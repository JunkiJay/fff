<?php

declare(strict_types=1);

namespace App\Services\Payments\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

readonly class PaymentSuccessResult implements Arrayable
{
    public function __construct() {}

    public function toArray(): array
    {
        return [];
    }
}