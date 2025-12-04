<?php

declare(strict_types=1);

namespace App\Services\Payments\Actions\Payments;

use App\Models\Payment;
use App\Services\Payments\DTO\CreatePaymentDTO;
use App\Services\Payments\Enum\PaymentStatusEnum;
use App\Services\Payments\Facades\PaymentServiceFacade;
use App\Services\Payments\Traits\PaymentProvidersResolver;
use App\Services\Payments\ValueObjects\PaymentErrorResult;
use App\Services\Payments\ValueObjects\PaymentRedirectResult;
use App\Services\Payments\ValueObjects\PaymentShowSBPResult;
use App\Services\Promocodes\PromocodeService;
use Carbon\Carbon;
use FKS\Actions\Action;

/**
 * @method static PaymentErrorResult|PaymentShowSBPResult|PaymentRedirectResult run(CreatePaymentDTO $dto)
 */
class PayAction extends Action
{
    use PaymentProvidersResolver;

    public function __construct(public readonly PromocodeService $promocodeService) {}

    public function handle(CreatePaymentDTO $dto): PaymentErrorResult|PaymentShowSBPResult|PaymentRedirectResult
    {
        $bonus = 0;
        $wager = 3;

        if ($dto->user->limit_payment) {
            throw new \DomainException('Платежи ограничены');
        }

        $paymentsCounts = PaymentServiceFacade::getPaymentsCounts($dto->user->id, Carbon::now()->subHour());
//        if (
//            $paymentsCounts->paymentsCounts[PaymentStatusEnum::PENDING->value] >= 10
//            && $paymentsCounts->paymentsCounts[PaymentStatusEnum::SUCCESS->value] === 0
//        ) {
//            throw new \DomainException('Пополнения заблокированы, попробуйте совершить платеж через час.');
//        }

        if ($dto->code !== null) {
            $promocodeBonus = $this->promocodeService->applyCode($dto->code, $dto->user);
            $bonus = $promocodeBonus->bonus;
            $wager = $promocodeBonus->wager;
        }

        $method = $this->resolveProviderConfig($dto->provider)
            ->getPaymentMethodConfig($dto->method);

        $amount = $dto->amount;

        // Если есть bonusPercent у метода, добавляем его к bonus (если нет промокода)
        if ($method->bonusPercent && $bonus == 0) {
            $bonus = $method->bonusPercent;
        }

        $payment = Payment::create([
            'user_id' => $dto->user->id,
            'sum' => $amount, // Исходная сумма БЕЗ бонусов
            'bonus' => $bonus, // Процент бонуса (из промокода или bonusPercent метода)
            'wager' => $wager,
            'system' => $dto->provider->value,
            'method' => $dto->method,
            'status' => PaymentStatusEnum::PENDING,
        ]);

        // Логируем для диагностики
        \Log::info('PayAction: payment created', [
            'payment_id' => $payment->id,
            'user_id' => $dto->user->id,
            'amount' => $amount,
            'bonus' => $bonus,
            'bonus_percent' => $method->bonusPercent ?? null,
            'provider' => $dto->provider->value,
            'method' => $dto->method->value,
        ]);

        $result = PayThoughtProviderAction::run($payment);

        if (!$result instanceof PaymentErrorResult) {

            $payment->merchant_meta = $result->orderId;
            $payment->save();
        }

        return $result;
    }
}