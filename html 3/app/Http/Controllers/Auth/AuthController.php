<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Mistery\MisteryServiceFacade;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'c_password' => 'required|string|min:8|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        preg_match('/^([^@]+)/', $request->email, $matches);
        $username = $matches[1] ?? '';


        // Получаем реферала из сессии
        $referralUniqueId = session('ref');
        $refUser = null;
        $referralUse = 0;
        if ($referralUniqueId) {
            $refUser = User::where('unique_id', $referralUniqueId)->first();
            if ($refUser) {
                $referralUse = $refUser->id;
            }
        }

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'unique_id' => Str::random(8),
            'username' => $username,
            'created_ip' => $this->getIp(),
            'used_ip' => $this->getIp(),
            'referral_use' => $referralUse,
        ]);

        // Аутентификация через сессию
        Auth::login($user);

        return response()->json([
            'user' => UserResource::make($user)
        ], 200);
    }

    public function consume(Request $request)
    {
        $raw = $request->query('token');

        if ($raw !== null) {
            $payload = JWT::decode($raw, new Key(config('sso.secret'), 'HS256'));
            if (isset($payload->sub)) {
                $user = User::query()->find($payload->sub);

                Auth::login($user);

                return response()->redirectTo('/');
            }
        }
    }

    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $request->session()->regenerate();

            // Сессия уже создана, дополнительно логин не требуется
            return response()->json([
                'user' => UserResource::make($user)
            ], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    // refreshToken не требуется для сессионной аутентификации

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();
        $token = Str::random(64);
        $user->update(['reset_token' => $token]);

        Mail::send('email.resetPassword', ['token' => $token], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Сброс пароля');
        });

        return response()->json(['message' => 'Ссылка для сброса пользователя успешно отправлен.']);
    }

    public function passwordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reset_token' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'c_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('reset_token', $request->reset_token)->first();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не существует!']);
        }

        $result = $user->update(['password' => bcrypt($request->password)]);

        if ($result) {
            return response()->json(['message' => 'Пароль успешно изменен']);
        } else {
            return response()->json(['message' => 'Произошла ошибка повторите попытку позже.']);
        }
    }

    public function user(Request $request)
    {
        if ($request->user()) {
            return response()->json([
                'user' => UserResource::make($request->user()),
                'config' => [
                    'tg_channel' => $this->config->tg_channel,
                    'tg_bot' => $this->config->tg_bot,
                    'vk_url' => $this->config->vk_url
                ]
            ], 200);
        }
        return response()->json([
            'error' => 'Пользователь не аутентифицирован',
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json([
            'message' => 'Успешно вышел из системы',
        ], 200);
    }
}
