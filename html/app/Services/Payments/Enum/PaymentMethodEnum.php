<?php

declare(strict_types=1);

namespace App\Services\Payments\Enum;

enum PaymentMethodEnum: string
{
    case CRYPTOBOT = 'cryptobot';
    case FK = 'fk';
    case SBP_QR = 'sbp_qr';
    case SBP = 'sbp';
    case C2C = 'c2c';
    case USDT = 'usdt';
}
