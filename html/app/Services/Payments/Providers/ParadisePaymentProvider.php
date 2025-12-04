<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Api\Paradise\ParadiseApiClient;
use App\Api\Paradise\Requests\ParadisePayRequest;
use App\Helpers\JsonFixer;
use App\Models\Payment;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentProviderConfig;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ParadisePaymentProvider extends PaymentProvider
{
    public function __construct(
        PaymentProviderConfig $config,
        public readonly ParadiseApiClient $apiClient
    ) {
        parent::__construct($config);
    }

    public function pay(Payment $payment): PaymentShowSBPResult|PaymentErrorResult
    {
        try {
            $result = $this->apiClient->pay(new ParadisePayRequest($payment->id, $payment->user_id, $payment->sum * 100, request()->ip()));
        } catch (ClientException $e) {
            $content = $e->getResponse()->getBody()->getContents();
            Log::error('Paradise API error', [
                'code' => $e->getCode(),
                'content' => $content,
                'payment_id' => $payment->id
            ]);

            try {
                $data = JsonFixer::decode($content);
                
                if (isset($data['errors']) && is_array($data['errors']) && count($data['errors']) > 0) {
                    $errorMessage = Arr::first($data['errors'])['message'] ?? 'Ошибка платежной системы';
                    
                    // Переводим английские сообщения на русский
                    if (stripos($errorMessage, 'No suitable cards found') !== false) {
                        $errorMessage = 'Нет доступных карт для обработки платежа. Попробуйте позже или выберите другой способ оплаты.';
                    }
                    
                    return new PaymentErrorResult($errorMessage);
                }
            } catch (\Exception $decodeException) {
                Log::error('Failed to decode Paradise error response', ['exception' => $decodeException]);
            }

            if ($e->getCode() === Response::HTTP_FORBIDDEN) {
                return new PaymentErrorResult('Доступ запрещен. Проверьте настройки платежной системы.');
            }

            return new PaymentErrorResult('Ошибка при создании платежа. Попробуйте позже.');
        } catch (\Throwable $e) {
            Log::error('Paradise payment error', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'exception' => $e
            ]);
            
            return new PaymentErrorResult('Ошибка при создании платежа. Попробуйте позже.');
        }


        if ($result->status === 'waiting') {
            Log::debug('Paradise response', ['response' => $result]);
            return new PaymentShowSBPResult(
                self::ACTION_SHOW_SBP_FORM,
                $result->paymentMethod->phone,
                $result->paymentMethod->name,
                $result->paymentMethod->bank,
                $result->amount / 100,
                $result->uuid,
            );
        }

        return new PaymentErrorResult('Не удалось найти реквизиты для оплаты');
    }
}