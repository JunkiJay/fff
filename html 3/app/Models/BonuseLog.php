<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property int $user_id
 * @property string $type
 * @property float $size
 * @property string|null $meta
 * @property string $created_at
 * @property string $updated_at
 */
class BonuseLog extends Model
{
    protected $guarded = [];

    protected $table = 'bonuse_logs';

}
