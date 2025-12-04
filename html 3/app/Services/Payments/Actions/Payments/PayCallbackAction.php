<?php

declare(strict_types=1);

namespace App\Services\Payments\Actions\Payments;

use App\Services\Payments\PaymentsService;
use App\Services\Payments\Traits\PaymentProvidersResolver;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentSuccessResult;
use FKS\Actions\Action;

class PayCallbackAction extends Action
{
    use PaymentProvidersResolver;

    public function __construct(public readonly PaymentsService $paymentsService) {}

    public function handle(string $paymentSecret, array $data): PaymentSuccessResult|PaymentErrorResult
    {
        $payment = $this->paymentsService->findPaymentBySecret($paymentSecret);

        if ($payment === null) {
            return new PaymentErrorResult('Payment not found');
        }

        return $this->resolveProvider($payment->system)->handleCreateCallback($payment, $data);
    }
}