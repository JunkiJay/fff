<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Tournaments;

use FKS\Search\Requests\SearchRequest;

class TournamentsListRequest extends SearchRequest
{
    public static function getAvailableFields(): array
    {
        return [];
    }

    public static function getSortingDefinitions(): array
    {
        return [
            'created_at',
        ];
    }
}