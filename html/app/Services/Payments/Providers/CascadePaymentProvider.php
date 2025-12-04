<?php

declare(strict_types=1);

namespace App\Services\Payments\Providers;

use App\Models\Payment;
use App\Services\Payments\Actions\Payments\PayThoughtProviderAction;
use App\Services\Payments\PaymentProvider;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use Illuminate\Support\Facades\Log;

class CascadePaymentProvider extends PaymentProvider
{
    public function pay(Payment $payment): PaymentRedirectResult|PaymentShowSBPResult|PaymentErrorResult
    {
        if (!$this->config->getPaymentMethodConfig($payment->method)) {
            throw new \DomainException('Выберите платежный метод');
        }

        $methodConfig = $this->config->getPaymentMethodConfig($payment->method);
        $errors = [];

        if (!is_array($methodConfig->cascade) && count($methodConfig->cascade) === 0) {
            throw new \DomainException('Выберите платежный метод');
        }

        foreach ($methodConfig->cascade as $item) {
            $payment->system = $item->value;
            try {
                $result = PayThoughtProviderAction::run($payment);

                // Если получили реквизиты СБП - сразу возвращаем их
                if ($result instanceof PaymentShowSBPResult) {
                    Log::info("Cascade: SBP result from {$item->value}", [
                        'provider' => $item->value,
                        'payment_id' => $payment->id,
                        'phone' => $result->phone ?? null,
                        'amount' => $result->amount ?? null
                    ]);
                    $payment->save();
                    return $result;
                }

                // Если ошибка - логируем и пробуем следующий провайдер
                if ($result instanceof PaymentErrorResult) {
                    $errors[] = $result->error;
                    Log::warning('Cascade payment error', [
                        'provider' => $item->value,
                        'error' => $result->error,
                        'payment_id' => $payment->id
                    ]);
                    continue;
                }
                
                // Если успешный результат (редирект) - возвращаем его
                $payment->save();
                return $result;
            } catch (\Throwable $e) {
                Log::error('Cascade payment exception', [
                    'provider' => $item->value,
                    'error' => $e->getMessage(),
                    'payment_id' => $payment->id,
                    'exception' => $e
                ]);
                // Продолжаем к следующему провайдеру
            }
        }

        Payment::find($payment->id)->forceDelete();

        if ($errors === []) {
            $errors[] = 'Попробуйте сделать платеж через 15 минут.';
        }

        return new PaymentErrorResult($errors);
    }
}