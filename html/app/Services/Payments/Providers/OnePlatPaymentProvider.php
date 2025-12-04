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

            Log::debug('OnePlat result', ['result' => $result]);

            return new PaymentShowSBPResult(
                self::ACTION_SHOW_SBP_FORM,
                $result->payment->note->pan,
                $result->payment->note->fio,
                $result->payment->note->bank,
                $result->payment->amountToPay,
                $result->guid,
            );
        } catch (ServerException $exception) {
            $content = $exception->getResponse()->getBody()->getContents();
            Log::error('OnePlat API error', [
                'code' => $exception->getCode(),
                'content' => $content,
                'payment_id' => $payment->id
            ]);
            
            try {
                $response = json_decode($content, true);
                $errorMessage = $response['error'] ?? 'Ошибка платежной системы';
                
                // Переводим английские сообщения на русский
                if (stripos($errorMessage, 'No suitable cards found') !== false) {
                    $errorMessage = 'Нет доступных карт для обработки платежа. Попробуйте позже или выберите другой способ оплаты.';
                }
                
                return new PaymentErrorResult($errorMessage);
            } catch (\Exception $decodeException) {
                Log::error('Failed to decode OnePlat error response', ['exception' => $decodeException]);
                return new PaymentErrorResult('Ошибка при создании платежа. Попробуйте позже.');
            }
        } catch (\Throwable $e) {
            Log::error('OnePlat payment error', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'exception' => $e
            ]);
            
            return new PaymentErrorResult('Ошибка при создании платежа. Попробуйте позже.');
        }
    }
}