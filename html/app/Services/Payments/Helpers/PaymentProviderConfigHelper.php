<?php

declare(strict_types=1);

namespace App\Services\Payments\Helpers;

use App\Services\Payments\Enum\PaymentProvidersEnum;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use FKS\Serializer\SerializerFacade;

class PaymentProviderConfigHelper
{
    public static function getConfig(PaymentProvidersEnum $provider): PaymentProviderConfig
    {
        $config = config('payments.providers.' . $provider->value);

        if ($config === null) {
            throw new \Exception('Config for provider ' . $provider->value . ' not found');
        }

        return SerializerFacade::deserializeFromArray($config, PaymentProviderConfig::class);
    }
}