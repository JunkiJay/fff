<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tournaments;

use App\Http\Requests\Api\V1\Tournaments\TournamentsListRequest;
use App\Services\Tournaments\TournamentsService;

class TournamentsController
{
    public function list(TournamentsListRequest $request, TournamentsService $service)
    {
        return $service->list($request->getSearchConditions());
    }
}