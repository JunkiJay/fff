<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Models\BonuseLog;
use App\Models\Payment;
use App\Models\User;
use App\Models\Withdraw;
use App\Repositories\Payments\PaymentsRepository;
use App\Repositories\Payments\WithdrawsRepository;
use App\Services\Payments\Collections\MethodsCollection;
use App\Services\Payments\Collections\PaymentSystemBalanceCollection;
use App\Services\Payments\DTO\PaymentProvidersDTO;
use App\Services\Payments\DTO\UserPaymentsCountsDTO;
use App\Services\Payments\Enum\PaymentProvidersEnum;
use App\Services\Payments\Enum\PaymentStatusEnum;
use App\Services\Payments\Traits\PaymentProvidersResolver;
use Carbon\Carbon;
use FKS\Search\Collections\EntitiesCollection;
use FKS\Search\ValueObjects\SearchConditions;
use FKS\Serializer\SerializerFacade;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PaymentsService
{
    use PaymentProvidersResolver;

    public function __construct(
        public readonly WithdrawsRepository $withdrawsRepository,
        public readonly PaymentsRepository $paymentsRepository,
    ) {}

    public function paymentProviders(User $user): PaymentProvidersDTO
    {
        $config = config('payment-providers');

        $providers = $config['providers'];
        $userPaymentsCount = $user->payments()->where('status', PaymentStatusEnum::SUCCESS->value)->count();

        foreach ($providers as $key => &$provider) {
            // Обрабатываем обе секции: payment и withdraw
            foreach (['payment', 'withdraw'] as $section) {
                $items = $provider[$section] ?? [];

                if (!is_array($items) || empty($items)) {
                    $provider[$section] = [];
                    continue;
                }

                $items = array_filter($items, static function (array $item) use ($userPaymentsCount): bool {
                    if (!empty($item['hidden']) && $item['hidden'] === true) {
                        return false;
                    }
                    if (isset($item['first_bonus_granted'])) {
                        if (
                            !BonuseLog::where(['user_id' => auth()->user()->id, 'type' => 'onetime'])->exists()
                            && !Payment::where(['user_id' => auth()->user()->id, 'status' => PaymentStatusEnum::SUCCESS->value])->exists()
                            && auth()->user()->balance === 0
                        ) {
                            return false;
                        }
                    }

                    if (isset($item['min_payments_count']) && is_numeric($item['min_payments_count'])) {
                        $minCount = (int)$item['min_payments_count'];
                        if ($userPaymentsCount < $minCount) {
                            return false;
                        }
                    }

                    return true;
                });

                usort($items, static function (array $a, array $b): int {
                    $posA = $a['position'] ?? PHP_INT_MAX;
                    $posB = $b['position'] ?? PHP_INT_MAX;
                    return $posA <=> $posB;
                });

                $provider[$section] = array_values($items);
            }

            // Если и payment, и withdraw пустые — провайдера можно убрать полностью
            if (empty($provider['payment']) && empty($provider['withdraw'])) {
                unset($providers[$key]);
            }
        }
        unset($provider);

        // Возвращаем в исходной структуре
        $config['providers'] = $providers;

        return SerializerFacade::deserializeFromArray($config, PaymentProvidersDTO::class);
    }

    public function methods(): MethodsCollection
    {
        return SerializerFacade::deserializeFromArray(config('payment-providers.methods'), MethodsCollection::class);
    }

    public function findDepositByExternalId(string $externalId): ?Payment
    {
        return Payment::query()
            ->where('merchant_meta', $externalId)
            ->first();
    }

    public function getBalance(PaymentProvidersEnum $provider): PaymentSystemBalanceCollection
    {
        return PaymentSystemBalanceCollection::make($this->resolveProvider($provider)->getBalance());
    }

    public function getProvideImage(Payment|Withdraw $model): ?string
    {
        if ($model->system === null) {
            return null;
        }

        $config = $this->resolveProviderConfig($model->system);

        if ($model instanceof Payment) {
            return $config?->getPaymentMethodConfig($model->method)?->image;
        }

        $methodConfig = $config?->getWithdrawMethodConfig($model->method);

        if ($model->variant === null) {
            return $methodConfig?->image;
        }

        return $methodConfig?->getVariantConfig($model->variant)?->image;
    }

    public function getWithdrawsList(SearchConditions $conditions): bool|EntitiesCollection|Builder|Collection
    {
        return $this->withdrawsRepository->search($conditions);
    }

    public function getPaymentsList(SearchConditions $conditions): bool|EntitiesCollection|Builder|Collection
    {
        return $this->paymentsRepository->search($conditions);
    }

    public function findPaymentBySecret(string $paymentSecret): ?Payment
    {
        return $this->paymentsRepository->findByWhere(['payment_secret' => $paymentSecret]);
    }

    public function getPaymentsCounts(int $userId, ?Carbon $createdFrom = null): UserPaymentsCountsDTO
    {
        $statuses = [
            PaymentStatusEnum::PENDING->value => 0,
            PaymentStatusEnum::SUCCESS->value => 0,
            PaymentStatusEnum::FAILED->value => 0,
        ];

        $counts = Payment::query()
            ->where('user_id', $userId)
            ->when($createdFrom, fn (Builder $q) => $q->where('created_at', '>=', $createdFrom))
            ->selectRaw('status, COUNT(*) AS cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        foreach ($counts as $status => $count) {
            $statuses[$status] = $count;
        }

        return new UserPaymentsCountsDTO($userId, $statuses);
    }
}