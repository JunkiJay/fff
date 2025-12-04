<?php

namespace App\Http\Requests\Api\V1\Payments;

use FKS\Search\Requests\FilteringDefinitions;
use FKS\Search\Requests\SearchRequest;

class PaymentsListRequest extends SearchRequest
{

    public static function getAvailableFields(): array
    {
        return [
            'id',
            'user_id',
            'sum',
            'system',
            'status',
            'status_human_name',
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
            'created_at',
        ];
    }
}