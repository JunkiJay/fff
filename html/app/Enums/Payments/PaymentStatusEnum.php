<?php

declare(strict_types=1);

namespace App\Enums\Payments;

class PaymentStatusEnum
{
    public const PENDING = 0;
    public const SUCCESS = 1;
    public const FAILED  = 2;
}