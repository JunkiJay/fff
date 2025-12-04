<?php

declare(strict_types=1);

namespace App\Services\Payments\Actions;

use App\Models\Payment;
use App\Services\Payments\DTO\CreatePaymentDTO;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use FKS\Actions\Action;

/**
 * @method static PaymentErrorResult|PaymentShowSBPResult|PaymentRedirectResult run(CreatePaymentDTO $dto)
 */
class PayAction extends Action
{
    public function handle(CreatePaymentDTO $dto): PaymentErrorResult|PaymentShowSBPResult|PaymentRedirectResult
    {
        $bonus = 0;
        $wager = 3;

        if ($dto->user->limit_payment) {
            throw new \DomainException('Платежи ограничены');
        }

        if ($dto->code !== null) {
            $promocodeBonus = $this->promocodeService->applyCode($dto->code, $dto->user);
            $bonus = $promocodeBonus->bonus;
            $wager = $promocodeBonus->wager;
        }

        $payment = Payment::create([
            'user_id' => $dto->user->id,
            'sum' => $dto->amount,
            'bonus' => $bonus,
            'wager' => $wager,
            'system' => $dto->provider->value,
            'method' => $dto->method,
        ]);

        $result = PayThoughtProviderAction::run($payment);

        if (
            !$result instanceof PaymentErrorResult
            && $result->orderId !== null
        ) {
            $payment->merchant_meta = $result->orderId;
            $payment->save();
        }

        return $result;
    }
}