<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;

class VkController extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function login($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        try {
            $user = json_decode(json_encode(Socialite::driver($provider)->stateless()->user()));
        } catch (\Exception $e) {
            return redirect('/')->withError('Попробуйте еще раз');
        }

        if (isset($user->returnUrl)) return redirect('/');
       
        return $this->createOrUpdateUser($user->user, $provider);
    }

    public function createOrUpdateUser($user, $provider)
    {
        $candidate = User::where('vk_id', $user->id)->first();
        $username = $user->first_name . ' ' . $user->last_name;

        Log::info('VK callback', [$user]);

        if (!$candidate) {
            $ref = User::where('unique_id', Session::get('ref'))->first();
            $ref_use = $ref ? $ref->id : 0;

            $candidate = User::create([
                'unique_id' => \Str::random(8),
                'username' => $username,
                'avatar' => $user->photo_200,
                'vk_id' => $user->id,
                'created_ip' => $this->getIp(),
                'used_ip' => $this->getIp(),
                'referral_use' => $ref_use,
                'email' => $user->email
            ]);
        }

        $candidate->update([
            'used_ip' => $this->getIp()
        ]);

        Auth::login($candidate, true);

       
        $accessToken = $candidate->createToken(
            'access_token',
            ['access-api'],
            Carbon::now()->addMinutes(config('sanctum.ac_expiration'))
        )->plainTextToken;

      
        $candidate->update(['auth_token' => $accessToken]);

        return redirect('/?token=' . $accessToken);
    }
}
