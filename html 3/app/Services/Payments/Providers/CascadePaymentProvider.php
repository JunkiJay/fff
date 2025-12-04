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

                if ($result instanceof PaymentShowSBPResult) {
                    Log::error("Show SBP", ['data' => $result]);
                }

                if ($result instanceof PaymentErrorResult) {
                    $errors[] = $result->error;

                    Log::error('Cascade payment error', ['error' => $result->error]);
                    continue;
                }
                $payment->save();

                return $result;
            } catch (\Throwable $e) {
                Log::error('Cascade payment error', ['error' => $e->getMessage(), 'exception' => $e]);
            }
        }

        Payment::find($payment->id)->forceDelete();

        if ($errors === []) {
            $errors[] = 'Попробуйте сделать платеж через 15 минут.';
        }

        return new PaymentErrorResult($errors);
    }
}