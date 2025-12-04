<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\PaymenProviders;


use FKS\Search\Requests\SearchRequest;

class WithdrawalMethodsListRequest extends SearchRequest
{
    public static function getAvailableFields(): array
    {
        return [
            'system',
            'withdraw',
            'base_currency',
        ];
    }

    public static function getSortingDefinitions(): array
    {
        // TODO: Implement getSortingDefinitions() method.
    }
}