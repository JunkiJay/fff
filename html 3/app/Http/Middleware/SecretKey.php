<?php

namespace App\Http\Middleware;

use Closure;

class SecretKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Получаем IP клиента
        $clientIp = $request->ip(); // автоматически учитывает прокси, если настроено доверие

        // Сравниваем с IP сервера
        if ($clientIp != $_SERVER['SERVER_ADDR']) {
            return response()->json('Invalid Request', 403);
        }

        return $next($request);
    }
}
