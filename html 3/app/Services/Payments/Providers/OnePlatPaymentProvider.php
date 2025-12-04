<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\OnePlat\OnePlatApiClient;
use App\Api\OnePlat\Requests\OnePlatPayRequest;
use App\Models\Payment;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

class OnePlatPaymentProvider extends PaymentProvider
{
    public function __construct(
        PaymentProviderConfig $config,
        public readonly OnePlatApiClient $apiClient
    ) {
        parent::__construct($config);
    }

    public function pay(Payment $payment): PaymentShowSBPResult|PaymentErrorResult
    {
        try {
            $result = $this->apiClient->createPayment(
                new OnePlatPayRequest($payment->id, $payment->user_id, $payment->sum)
            );

            Log::error('OnePlat result', ['result' => $result]);

            return new PaymentShowSBPResult(
                self::ACTION_SHOW_SBP_FORM,
                $result->payment->note->pan,
                $result->payment->note->fio,
                $result->payment->note->bank,
                $result->payment->amountToPay,
                $result->guid,
            );
        } catch (ServerException $exception) {
            if ($exception->getCode() === 500) {
                $response = json_decode($exception->getResponse()->getBody()->getContents(), true);
                Log::error('OnePlat error', ['error' => $response['error']]);
                return new PaymentErrorResult($response['error']);
            }

            throw $exception;
        }
    }
}