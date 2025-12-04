<?php

declare(strict_types=1);

namespace App\Repositories\Payments;

use App\Models\Withdraw;
use FKS\Repositories\Repository;
use FKS\Search\Repositories\SearchRepository;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Repository<Withdraw>
 */
class WithdrawsRepository extends SearchRepository
{
    public static function getEntityInstance(): Model
    {
        return Withdraw::make();
    }

    public static function getMapAvailableFieldToWith(): array
    {
        return [
            'user' => 'user',
        ];
    }

    public static function excludeAvailableFieldsFromSelect(): array
    {
        return [
            'status_human_name',
        ];
    }

    public static function getMapAvailableFieldToSelect(): array
    {
        return [
            'image' => [
                'system',
                'method',
                'variant'
            ]
        ];
    }
}