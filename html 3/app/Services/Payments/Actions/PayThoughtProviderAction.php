<?php

declare(strict_types=1);

namespace App\Services\Payments\Actions;

use App\Models\Payment;
use App\Services\Payments\Traits\PaymentProvidersResolver;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use FKS\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @method static PaymentErrorResult|PaymentShowSBPResult|PaymentRedirectResult dispatch(Payment $payment)
 * @method static PaymentErrorResult|PaymentShowSBPResult|PaymentRedirectResult run(Payment $payment)
 */
class PayThoughtProviderAction extends Action
{
    use PaymentProvidersResolver;

    public function handle(Payment $payment): PaymentErrorResult|PaymentShowSBPResult|PaymentRedirectResult
    {
        return DB::transaction(function () use ($payment) {
            $this->validatePayment($payment);

            return $this->resolveProvider($payment->system)->pay($payment);
        });
    }

    private function validatePayment(Payment $payment): void
    {
        $config = $this->resolveProviderConfig($payment->system)
            ->getPaymentMethodConfig($payment->method);

        if ($config === null) {
            throw new \Exception("Payment method $payment->method not found");
        }

        $validationRules = [
            'amount' => 'required|numeric|min:' . $config->min . '|max:' . $config->max,
        ];

        Validator::make(['amount' => $payment->sum], $validationRules)->validate();
    }
}