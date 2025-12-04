<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

class UsersController
{
    public function get()
    {
        $user = auth()->user();

        return [
            'id' => $user->id,
            'unique_id' => $user->unique_id,
            'balance' => $user->balance,
            'avatar' => $user->avatar,
            'username' => $user->username,
            'vk_id' => $user->vk_id,
            'tg_id' => $user->tg_id,
            'is_worker' => $user->is_worker,
            'is_admin' => $user->is_admin
        ];
    }
}