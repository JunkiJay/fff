<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\GTX\GTXApiClient;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;

class GTXPaymentProvider extends PaymentProvider
{
    public function __construct(
        PaymentProviderConfig $config,
        public readonly GTXApiClient $apiClient
    ) {
        parent::__construct($config);
    }
}