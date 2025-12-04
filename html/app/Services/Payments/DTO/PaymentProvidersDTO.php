<?php

declare(strict_types=1);

namespace App\Services\Payments\DTO;

use App\Models\User;
use App\Services\Payments\Collections\MethodsCollection;
use App\Services\Payments\Collections\PaymentProvidersConfigCollection;
use FKS\Serializer\SerializableObject;

class PaymentProvidersDTO extends SerializableObject
{
    public function __construct(
        public PaymentProvidersConfigCollection $providers,
        public MethodsCollection $methods,
    ) {
    }

    public function toArray(): array
    {
        return [
            'providers' => $this->providers->toArray(),
            'methods' => $this->methods->toArray(),
        ];
    }
}