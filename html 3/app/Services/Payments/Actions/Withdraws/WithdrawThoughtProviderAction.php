<?php

declare(strict_types=1);

namespace App\Services\Payments\Actions\Withdraws;

use App\Enums\Payments\WithdrawStatusEnum;
use App\Models\Withdraw;
use App\Services\Actions\Actions\ActionCreateAction;
use App\Services\Actions\DTO\ActionCreateDTO;
use App\Services\Payments\Traits\PaymentProvidersResolver;
use App\Services\Payments\ValueObjects\WithdrawResult;
use FKS\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * @method static WithdrawResult run(Withdraw|int $withdraw, ?string $reason = null)
 */
class WithdrawThoughtProviderAction extends Action
{
    use PaymentProvidersResolver;

    public function handle(Withdraw|int $withdraw, ?string $reason = null): WithdrawResult
    {
        if (!$withdraw instanceof Withdraw) {
            $withdraw = Withdraw::find(intval($withdraw));
        }

        if (!$withdraw) {
            throw new \DomainException('Выплата отменена пользователем');
        }

        $this->validateWithdraw($withdraw);

        try {
            $result = $this->resolveProvider($withdraw->system)
                ->withdraw($withdraw);

            Log::info('Withdraw result: ', ['status' => $result->status, 'message' => $result->message]);

            if ($result->status === WithdrawStatusEnum::SUCCESS) {
                $this->completeWithdraw($withdraw, $reason);
                $withdraw->update([
                    'status' => WithdrawStatusEnum::SUCCESS,
                    'reason' => $reason,
                ]);
            } elseif ($result->status === WithdrawStatusEnum::ALREADY_SENT) {
                $withdraw->update([
                    'status' => WithdrawStatusEnum::SUCCESS,
                    'reason' => $reason,
                ]);

                $this->completeWithdraw($withdraw, $reason);
            } else {
                Log::error('Withdraw error: ', ['message' => $result->message]);
            }
        } catch (\Throwable $exception) {
            ActionCreateAction::run(
                new ActionCreateDTO(
                    auth()->user()->id,
                    'Вывод не удался ' . $exception->getMessage(),
                    0,
                    0,
                    $exception->getMessage()
                )
            );

            Log::error('Withdraw result: ', ['message' => $exception->getMessage()]);

            $withdraw->update([
                'status' => WithdrawStatusEnum::PENDING
            ]);

            return new WithdrawResult(true, WithdrawStatusEnum::PENDING);
        }

        return $result;
    }

    private function validateWithdraw(Withdraw $withdraw): void
    {
        $config = $this->resolveProviderConfig($withdraw->system)
            ->getWithdrawMethodConfig($withdraw->method);

        if ($config === null) {
            throw new \Exception("Withdraw method $withdraw->method not found");
        }

        $validationRules = [
            'amount' => 'required|numeric',
            'status' => 'required|in:0',
        ];
        $messages = [
            'status' => 'Статус выплаты уже изменен ранее',
        ];

        $validationRules['amount'] .= '|min:' . $config->min;
        $messages['amount.min'] = "Минимальная сумма вывода $config->min ₽";

        Validator::make(
            [
                'amount' => $withdraw->sum,
                'status' => $withdraw->status,
                'system' => $withdraw->system,
            ],
            $validationRules,
            $messages
        )->validate();
    }

    public function completeWithdraw(Withdraw $withdraw, string $reason = ''): void
    {
        $withdraw->update([
            'status' => WithdrawStatusEnum::SUCCESS,
            'reason' => $reason,
        ]);
    }
}