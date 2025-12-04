<?php

declare(strict_types=1);

namespace App\Services\Payments\Actions\Withdraws;

use App\Models\Withdraw;
use App\Services\Payments\Enum\WithdrawStatusEnum;
use App\Services\Payments\Traits\PaymentProvidersResolver;
use App\Services\Payments\ValueObjects\WithdrawResult;
use FKS\Actions\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @method static WithdrawResult run(Withdraw|int $withdraw, ?string $reason = null)
 */
class WithdrawSendToProviderAction extends Action
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

        if ($withdraw->status !== WithdrawStatusEnum::CREATE->value) {
            throw new \DomainException('Статус выплаты уже изменен ранее');
        }

        try {
            $result = DB::transaction(function () use ($withdraw, $reason) {
                Log::info('Withdraw start: ', ['withdraw_id' => $withdraw->id, 'reason' => $reason]);

                $result = $this->resolveProvider($withdraw->system)->withdraw($withdraw);

                Log::info('Withdraw result: ', ['withdraw_id' => $withdraw->id, 'status' => $result->status->value, 'message' => $result->message]);

                if (
                    $result->status === WithdrawStatusEnum::SUCCESS
                    || $result->status === WithdrawStatusEnum::ALREADY_SENT
                ) {
                    $this->completeWithdraw($withdraw, $reason);
			
                    return new WithdrawResult(true, WithdrawStatusEnum::SUCCESS);
                } elseif ($result->status === WithdrawStatusEnum::FRAUD_DETECTED) {
                    \Log::error('Withdraw fraud detected', ['user_id' => $withdraw->user_id, 'withdraw_id' => $withdraw->id, 'message' => $result->message]);
                    throw new \Exception('Fraud detected');
                } else {
                    return WithdrawRollbackToPending::run($withdraw, $result->message);
                }
            });
        } catch (\Throwable $exception) {
            Log::error('Withdraw error: ', ['message' => $exception->getMessage()]);

            WithdrawRollbackToPending::run($withdraw, $exception->getMessage());

            return new WithdrawResult(true, WithdrawStatusEnum::CREATE);
        }

        return $result;
    }

    public function completeWithdraw(Withdraw $withdraw, ?string $reason = ''): void
    {
        $withdraw->update([
            'status' => WithdrawStatusEnum::SUCCESS,
            'reason' => $reason,
        ]);
    }
}