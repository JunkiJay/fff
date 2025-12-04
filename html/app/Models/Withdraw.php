<?php

namespace App\Models;

use App\Services\Currencies\Enums\CurrenciesEnum;
use App\Services\Currencies\Facades\CurrencyConverterFacade;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\WithdrawStatusEnum;
use App\Services\Payments\Facades\PaymentServiceFacade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $id
 * @property int $user_id
 * @property float $sum
 * @property float $sumWithCom
 * @property string $wallet
 * @property string $system
 * @property string|null $reason
 * @property int $status
 * @property int $fake
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int $is_youtuber
 * @property PaymentMethodEnum|null $method
 * @property string $variant
 */
class Withdraw extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethodEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getImageAttribute(): ?string
    {
        return PaymentServiceFacade::getProvideImage($this);
    }

    public function getStatusHumanNameAttribute(): string
    {
        return WithdrawStatusEnum::tryFrom($this->status)->getHumanName();
    }

    public function getUsdtAttribute()
    {
        return CurrencyConverterFacade::convert(CurrenciesEnum::RUB, CurrenciesEnum::USDT, $this->sum);
    }
}
