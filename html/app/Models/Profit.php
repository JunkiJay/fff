<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int|null $id
 * @property float $bank_dice
 * @property float $bank_mines
 * @property float $bank_bubbles
 * @property float $bank_wheel
 * @property float $bank_plinko
 * @property float $earn_bubbles
 * @property int $comission
 * @property float $earn_dice
 * @property float $earn_mines
 * @property float $earn_plinko
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Profit extends Model
{
    protected $table = 'profit';

    protected $fillable = [
        'bank_dice',
        'bank_mines',
        'bank_bubbles',
        'bank_wheel',
        'comission',
        'earn_dice',
        'earn_mines',
        'earn_bubbles',
        'bank_plinko',
        'earn_plinko'
    ];
}
