<?php

declare(strict_types=1);

namespace App\Api\FK\Responses;

use App\Api\FK\Entities\FKHistory;
use FKS\Api\DTO\ApiResponse;

class FKHistoryResponse extends ApiResponse
{
    /**
     * @param FKHistory[] $data
     */
    public function __construct(mixed $data = [])
    {
        parent::__construct($data);
    }

}