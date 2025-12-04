<?php

declare(strict_types=1);

namespace App\Services\Payments\Enum;

enum PaymentProviderIconsEnum: string
{
    case FK = '/assets/withdraw/bank-fk.png';
    case CRYPTOBOT = '/assets/withdraw/bank-cryptobot.png';
    case SBP = "/assets/withdraw/bank-sb.png";
    case CARD = "/assets/withdraw/bank-cards.png";
    case USDT = '/assets/withdraw/bank-usdc.png';
}
