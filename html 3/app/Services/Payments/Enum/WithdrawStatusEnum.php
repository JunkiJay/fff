<?php

declare(strict_types=1);

namespace App\Services\Payments\Enum;

enum WithdrawStatusEnum: int
{
    case CREATE = 0;
    case SUCCESS = 1;
    case DECLINE  = 2;
    case PENDING  = 3;
    case ALREADY_SENT  = 4;
    case FRAUD_DETECTED  = 5;

    public function getHumanName(): string
    {
        return match ($this) {
            self::CREATE => 'Создан',
            self::SUCCESS => 'Выполнено',
            self::DECLINE => 'Отклонено',
            self::PENDING => 'В обработке',
            self::ALREADY_SENT => 'Отправлено ранее',
            self::FRAUD_DETECTED => 'Fraud',
        };
    }
}
