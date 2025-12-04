<?php

declare(strict_types=1);

namespace App\Services\Payments\Collections;

use App\Services\Payments\ValueObjects\WithdrawVariant;
use Illuminate\Support\Collection;

/**
 * @extends Collection<WithdrawVariant>
 */
class WithdrawVariantsCollection extends Collection
{
}