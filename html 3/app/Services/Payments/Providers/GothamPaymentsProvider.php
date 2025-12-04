<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\Gotham\GothamApiClient;
use App\Api\Gotham\Requests\GothamCreatePaymentRequest;
use App\Models\Payment;
use App\Services\Currencies\Enums\CurrenciesEnum;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use App\Services\Payments\ValueObjects\PaymentSuccessResult;
use Illuminate\Support\Facades\Log;

class GothamPaymentsProvider extends PaymentProvider
{

    public function __construct(
        PaymentProviderConfig $config,
        private readonly GothamApiClient $apiClient
    ) {
        parent::__construct($config);
    }

    public function pay(Payment $payment): PaymentShowSBPResult|PaymentErrorResult|PaymentRedirectResult
    {
        if ($payment->sum < 999) {
            $response = $this->apiClient->createCardNumberOrder(
                new GothamCreatePaymentRequest(
                    (int) $payment->sum,
                    CurrenciesEnum::RUB,
                    (string) $payment->id,
                    $payment->callback_secret
                )
            );
        } else {
            $this->apiClient->createSBPOrder(
                new GothamCreatePaymentRequest(
                    (int) $payment->sum,
                    CurrenciesEnum::RUB,
                    (string) $payment->id,
                    $payment->callback_secret
                )
            );
        }
    }

    public function handleCreateCallback(Payment $payment, array $data): PaymentSuccessResult|PaymentErrorResult
    {
        Log::debug('Gotham callback', ['data' => $data]);

        return new PaymentSuccessResult();
    }
}