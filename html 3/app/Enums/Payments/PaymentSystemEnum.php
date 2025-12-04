<?php

declare(strict_types=1);

namespace App\Enums\Payments;

enum PaymentSystemEnum: string
{
    case FK = 'fk';
    case CRYPTOBOT = 'cryptobot';
}
