<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\Expay\ExpayApiClient;
use App\Api\Expay\Requests\CreatePaymentRequest;
use App\Models\Payment;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use Illuminate\Support\Facades\Log;

class ExpayPaymentProvider extends PaymentProvider
{
    public function __construct(PaymentProviderConfig $config, public readonly ExpayApiClient $apiClient)
    {
        parent::__construct($config);
    }

    public function pay(Payment $payment): PaymentRedirectResult
    {
        try {
            $result = $this->apiClient->createPayment(
                new CreatePaymentRequest($payment->sum, (string) $payment->id, (string) $payment->user_id)
            );

            return new PaymentRedirectResult(self::ACTION_REDIRECT, $result->alterRefer, $result->trackerId);
        } catch (\Throwable $e) {
            Log::error('Expay error', ['error' => $e->getMessage()]);

            throw new \DomainException('Не удалось создать платеж');
        }
    }
}