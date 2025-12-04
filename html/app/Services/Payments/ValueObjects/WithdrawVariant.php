<?php

declare(strict_types=1);

namespace App\Services\Payments\ValueObjects;

use FKS\Serializer\SerializableObject;

class WithdrawVariant extends SerializableObject
{
    public function __construct(
        public string $title,
        public string $name,
        public string $image
    ) {
    }
}