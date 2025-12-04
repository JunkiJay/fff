<?php

namespace App\Http\Requests\Api\V1\Payments;

use FKS\Search\Requests\SearchRequest;

class PaynentsListRequest extends SearchRequest
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

    public static function getSortingDefinitions(): array
    {
        return [
            'created_at',
        ];
    }
}