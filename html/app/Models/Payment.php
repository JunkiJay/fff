<?php

namespace App\Models;

use App\Models\Traits\LockerTrait;
use App\Services\Payments\Enum\PaymentMethodEnum;
use App\Services\Payments\Enum\PaymentStatusEnum;
use App\Services\Payments\Facades\PaymentServiceFacade;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int|null $id
 * @property int $user_id
 * @property float $sum
 * @property float $bonus
 * @property float|null $wager
 * @property int $status
 * @property string|null $system
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $merchant_meta
 * @property PaymentMethodEnum|null $method
 * @property string $callback_secret
 * @property User $user
 */
class Payment extends Model
{
    use LockerTrait;

    protected $guarded = [];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Payment $payment) {
            $payment->handleCreating();

            if (empty($payment->callback_secret)) {
                $payment->callback_secret = bin2hex(random_bytes(32));
            }
        });
    }

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
        return PaymentStatusEnum::tryFrom($this->status)->getHumanName();
    }
}
