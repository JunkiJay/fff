<?php

declare(strict_types=1);

namespace App\Services\Payments\Enum;

enum PaymentStatusEnum: int
{
    case PENDING = 0;
    case SUCCESS = 1;
    case FAILED  = 2;

    public function getHumanName(): string
    {
        return match ($this) {
            self::PENDING => 'Ожидание',
            self::SUCCESS => 'Зачислено на счет',
            self::FAILED => 'Неудача',
        };
    }
}
