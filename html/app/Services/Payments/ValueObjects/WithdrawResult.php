<?php

declare(strict_types=1);

namespace App\Services\Payments\ValueObjects;

use App\Services\Payments\Enum\WithdrawStatusEnum;
use FKS\Serializer\SerializableObject;

class WithdrawResult extends SerializableObject
{
    public function __construct(
        public bool $success,
        public WithdrawStatusEnum $status,
        public ?string $message = null
    ) {
    }
}