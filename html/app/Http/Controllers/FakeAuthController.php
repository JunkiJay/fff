<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FakeAuthController extends Controller
{
    public function fakeAuth(int $id)
    {
        if (!app()->environment('local')) abort(404);
        $user = Auth::loginUsingId($id);
//        $accessToken = $user->createToken(
//            'access_token',
//            ['access-api'],
//            Carbon::now()->addMinutes(config('sanctum.ac_expiration'))
//                ->toDateTimeString()
//        )->plainTextToken;
//        $refreshToken = $user->createToken(
//            'refresh_token',
//            ['issue-access-token'],
//            Carbon::now()->addMinutes(config('sanctum.rt_expiration'))->toDateTimeString()
//        )->plainTextToken;
//
//        $user->update(['auth_token' => $accessToken]);

        return redirect('/');
    }
}
