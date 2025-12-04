<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function init()
    {
        if (!$this->user) {
            return [
                'message' => 'Вы не авторизованы'
            ];
        }

        return [
            'user' => [
                'id' => $this->user->id,
                'unique_id' => $this->user->unique_id,
                'balance' => $this->user->balance,
                'avatar' => $this->user->avatar,
                'username' => $this->user->username,
                'vk_id' => $this->user->vk_id,
                'tg_id' => $this->user->tg_id,
                'is_worker' => $this->user->is_worker,
                'is_admin' => $this->user->is_admin
            ],
            'config' => [
                'tg_channel' => $this->config->tg_channel,
                'tg_bot' => $this->config->tg_bot,
                'vk_url' => $this->config->vk_url
            ]
        ];
    }

    public function ranks()
    {
        return Rank::orderBy('id', 'asc')->get();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function videocardUpdate(Request $r)
    {
        if (Auth::guest()) return;

        $this->user->update([
            'videocard' => $r->video
        ]);
    }

    public function fingerprintUpdate(Request $r)
    {
        if (Auth::guest()) return;

        $this->user->update([
            'fingerprint' => $r->finger
        ]);
    }

    public function repostVK(Request $data)
    {
        switch ($data->type) {
            case 'wall_repost':
                return 200;

            case 'confirmation':
                return '62a7c707'; // PIZDEC

            default:
                return NULL;
        }
    }
}
