<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Withdraws;

use FKS\Search\Requests\FilteringDefinitions;
use FKS\Search\Requests\SearchRequest;

class WithdrawsListRequest extends SearchRequest
{
    public static function getAvailableFields(): array
    {
        return [
            'id',
            'user_id',
            'sum',
            'sumWithCom',
            'reason',
            'system',
            'method',
            'variant',
            'status',
            'status_human_name',
            'wallet',
            'image',
            'created_at',
        ];
    }

    public static function getFilteringDefinitions(): FilteringDefinitions
    {
        $definitions = new FilteringDefinitions();

        $definitions->containsInteger('status');
        $definitions->containsInteger('user_id');

        return $definitions;
    }

    public static function getSortingDefinitions(): array
    {
        return [
            'created_at'
        ];
    }
}