<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramBinding;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

final class TelegramBindingController extends Controller
{
    public function generate(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Удаляем старые коды для пользователя
        TelegramBinding::where('user_id', $user->id)->delete();

        $code = Str::random(32);
        $expiresAt = Carbon::now()->addMinutes(10);

        $binding = TelegramBinding::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'code' => $code,
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);
    }
}
