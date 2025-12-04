<?php

namespace App\Services\Payments\Collections;

use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use Illuminate\Support\Collection;

/**
 * @extends Collection<PaymentProviderConfig>
 */
class PaymentProvidersConfigCollection extends Collection
{
    public function withdrawProviders(): PaymentProvidersConfigCollection
    {
        return $this->filter(static function (PaymentProviderConfig $config) {
            return !empty($config->withdraw);
        });
    }

    public function paymentsProviders(): PaymentProvidersConfigCollection
    {
        return $this->filter(static function (PaymentProviderConfig $config) {
            return !empty($config->payment);
        });
    }

    public function getPaymentMethods(): PaymentProvidersConfigCollection|Collection
    {
        return $this->map(static function (PaymentProviderConfig $config) {
            $methods = [];

            foreach ($config->getPaymentMethods() as $paymentMethod) {
                $methods[] = [
                    ...$paymentMethod,
                    'provider' => $config->provider->value
                ];
            }

            return $methods;
        })->collapse();
    }

    public function getWithdrawMethods(): PaymentProvidersConfigCollection|Collection
    {
        return $this->map(static function (PaymentProviderConfig $config) {
            return $config->getWithdrawMethods();
        })->collapse();
    }
}