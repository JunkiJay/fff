<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final /**
 * @property int|null $id
 * @property int $user_id
 * @property string $code
 * @property string $expires_at
 * @property string $created_at
 * @property string $updated_at
 */
class TelegramBinding extends Model
{
    protected $table = 'telegram_bindings';

    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
