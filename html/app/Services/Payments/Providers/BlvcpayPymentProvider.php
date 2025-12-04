<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\Blvckpay\BlvckpayApiClient;
use App\Models\Payment;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;

class BlvcpayPymentProvider extends PaymentProvider
{
    public function __construct(
        PaymentProviderConfig $config,
        private readonly BlvckpayApiClient $apiClient
    ) {
        parent::__construct($config);
    }

    public function pay(Payment $payment): PaymentRedirectResult
    {
        $response = $this->apiClient->createSbpOrder($payment->sum);


        return new PaymentRedirectResult(
            self::ACTION_REDIRECT,
            $this->apiClient->getQR($response->orderId),
            $response->orderId
        );
    }
}