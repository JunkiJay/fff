<?php

declare(strict_types=1);

namespace App\Services\Payments\Collections;

use App\Services\Payments\ValueObjects\WithdrawalMethodConfig;
use Illuminate\Support\Collection;

/**
 * @extends Collection<WithdrawalMethodConfig>
 */
class WithdrawalMethodCollection extends Collection
{
}