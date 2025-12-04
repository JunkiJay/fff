<?php

declare(strict_types=1);

namespace App\Services\Payments\Actions\Withdraws;

use App\Helpers\SettingsHelper;
use App\Models\User;
use App\Models\Withdraw;
use App\Services\Actions\Actions\ActionCreateAction;
use App\Services\Actions\DTO\ActionCreateDTO;
use App\Services\Currencies\Enums\CurrenciesEnum;
use App\Services\Currencies\Facades\CurrencyConverterFacade;
use App\Services\Payments\DTO\CreateWithdrawDTO;
use App\Services\Payments\Enum\PaymentStatusEnum;
use App\Services\Payments\Enum\WithdrawStatusEnum;
use App\Services\Payments\Traits\PaymentProvidersResolver;
use Carbon\Carbon;
use DomainException;
use FKS\Actions\Action;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * @method static Withdraw run(CreateWithdrawDTO $dto)
 */
class WithdrawAction extends Action
{
    use PaymentProvidersResolver;

    public function handle(CreateWithdrawDTO $dto): Withdraw
    {
			$withdraw = DB::transaction(function () use ($dto) {
            $providerConfig = $this->resolveProviderConfig($dto->provider);
            $methodConfig = $providerConfig->getWithdrawMethodConfig($dto->method);
            $this->validateWithdraw($dto);

            $antifraudCacheKey = "withdraw_" . $dto->user->id;

            usleep(150 * random_int(1, 80));
            if (Cache::has($antifraudCacheKey)) {
                throw new DomainException('Выплата заблокирована на 1 минуту');
            }
            Cache::put($antifraudCacheKey, true, 60);

            $user = User::where('id', $dto->user->id)->lockForUpdate()->first();

            $amount = $dto->amount;

            $user->decrement('balance', $amount);

            $withdraw = Withdraw::create([
                'user_id' => $user->id,
                'wallet' => $dto->wallet,
                'system' => $dto->provider->value,
                'sum' => $dto->amount,
                'sumWithCom' => $dto->amount * ((100 - $methodConfig->commissionPercents) / 100),
                'method' => $dto->method,
                'variant' => $dto->variant,
                'fake' => $user->is_worker,
                'is_youtuber' => $dto->user->is_youtuber,
                'status' => $user->is_worker ? WithdrawStatusEnum::SUCCESS->value : WithdrawStatusEnum::CREATE->value,
            ]);

            ActionCreateAction::run(
                new ActionCreateDTO(
                    $user->id,
                    "Вывод через {$dto->provider->value}",
                    round($user->balance + $amount, 2),
                    round($user->balance, 2)
                )
            );

            return $withdraw;
        });
		

		
        if ($this->isAutoWithdrawAvailable($withdraw)) {
			
		$message = urlencode('🔥 Задействован автовывод игроку с id - '.$withdraw->user_id.'
		Сумма выплаты - '.$withdraw->sum.'. На кошелек - '.$withdraw->wallet.'');
		
		$url = file_get_contents('https://api.telegram.org/bot7158462822:AAHgt-VuXoGr-E5wXd3lqBzNTM_gWhP_V9w/sendMessage?chat_id=-4967657255&text='.$message.'');
		
            WithdrawSendToProviderAction::run($withdraw, 'без подтверждения модератора');
        }

        return $withdraw;
    }

    public function validateWithdraw(CreateWithdrawDTO $dto): void
    {
        $settings = SettingsHelper::getSettings();
        if ($dto->user->withdraws()->where('status', WithdrawStatusEnum::CREATE->value)->count() >= $settings->withdraw_request_limit) {
            throw new DomainException('Дождитесь предыдущих выводов');
        }

        $paymentSumPerDays = $dto->user->payments()->where([['created_at', '>=', Carbon::today()->subDays($settings->deposit_per_n)], ['status', PaymentStatusEnum::SUCCESS->value]])->sum('sum');

        if ($paymentSumPerDays < $settings->deposit_sum_n && !$dto->user->is_youtuber) {
            throw new DomainException('Необходимо пополнить баланс на ' . $settings->deposit_sum_n . ' руб за последние ' . $settings->deposit_per_n . ' дней');
        }

        $method = $this->resolveMethodConfig($dto->method);
        $providerMethod = $this->resolveProviderConfig($dto->provider)
            ->getWithdrawMethodConfig($dto->method);

        $data = [
            'wallet' => $dto->wallet,
            'amount' => $dto->amount,
            'wager' => $dto->user->wager,
            'slots_wager' => $dto->user->slots_wager,
        ];

        $rules = [
            'wallet' => $method->walletValidationRules,
            'amount' => "numeric|min:$providerMethod->min|max:{$dto->user->balance}",
        ];

        if ($dto->user->wager_status === 1) {
            $rules['wager']  = 'numeric|max:0';
            $rules['slots_wager'] = 'numeric|max:0';
        }

        $walletErrors = Arr::mapWithKeys($method->walletValidationErrors, fn ($error, $key) => ['wallet.' . $key => $error]);

        $errors = array_merge(
            [
                'amount.min' => "Минимальная сумма вывода $providerMethod->min руб.",
                'amount.max' => "Недостаточно средств на счету",
                'wager.max' => "Необходимо отыграть еще {$dto->user->wager}",
                'slots_wager.max' => "Необходимо отыграть еще {$dto->user->slots_wager}",
            ],
            $walletErrors,
        );

        if (SettingsHelper::getSettings()->min_dep_withdraw !== null) {
            $data['total_payments'] = $dto->user->payments()->where('status', PaymentStatusEnum::SUCCESS->value)->sum('sum');
            $rules['total_payments'] = "min:" . SettingsHelper::getSettings()->min_dep_withdraw;
            $errors['total_payments.min'] = "min:" . SettingsHelper::getSettings()->min_dep_withdraw;
        }

        Validator::validate(
            $data,
            $rules,
            $errors
        );
    }

    public function isAutoWithdrawAvailable(Withdraw $withdraw): bool
    {
        return $this->resolveProvider($withdraw->system)?->isAutoWithdrawAvailable($withdraw) ?? false;
    }
}