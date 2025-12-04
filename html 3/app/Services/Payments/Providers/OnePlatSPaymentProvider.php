<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\OnePlatS\OnePlatSApiClient;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;

class OnePlatSPaymentProvider extends PaymentProvider
{
    public function __construct(
        PaymentProviderConfig $config,
        public readonly OnePlatSApiClient $apiClient
    ) {
        parent::__construct($config);
    }
}