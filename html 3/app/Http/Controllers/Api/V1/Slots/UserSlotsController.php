<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Slots;

use App\Services\Slots\UserSlotsService;

class UserSlotsController
{
    public function last(int|string $userId, UserSlotsService $userSlotsService)
    {
        return $userSlotsService->last(intval($userId));
    }
}