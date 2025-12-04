<?php

declare(strict_types=1);

namespace App\Enums\Payments;

class WithdrawStatusEnum
{
    public const int CREATE = 0;
    public const int SUCCESS = 1;
    public const int DECLINE  = 2;
    public const int PENDING  = 3;
    public const int ALREADY_SENT  = 4;
    public const int FRAUD_DETECTED  = 5;

    public static function values(): array
    {
        return array_values((new \ReflectionClass(self::class))->getConstants());

    }
}