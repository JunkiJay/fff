<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceMaintenanceMode
{
    /**
     * Разрешенные IP адреса (могут заходить на сайт во время техработ)
     *
     * @var array
     */
    protected $allowedIps = [
        '146.103.103.119',
        '62.4.35.17',
        '45.137.212.30', // IP сервера
        '146.103.121.62', // Разрешенный IP
    ];

    /**
     * Пароль для доступа во время техработ
     *
     * @var string
     */
    protected $maintenancePassword = 'uMc4nBT';

    /**
     * Получить реальный IP адрес клиента (учитывая прокси и DDoS Guard)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getRealIp(Request $request)
    {
        // Список заголовков для проверки (в порядке приоритета)
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',            // Nginx
            'HTTP_X_FORWARDED_FOR',      // Прокси
            'HTTP_X_FORWARDED',          // Альтернативный прокси
            'HTTP_CLIENT_IP',            // Клиентский IP
        ];

        foreach ($headers as $header) {
            $ip = $request->server($header);
            if (!empty($ip)) {
                // Если несколько IP через запятую, берем первый
                $ip = explode(',', $ip)[0];
                $ip = trim($ip);
                
                // Проверяем валидность IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Если ничего не найдено, используем REMOTE_ADDR
        $ip = $request->server('REMOTE_ADDR');
        
        // Убираем порт если есть
        if (strpos($ip, ':') !== false) {
            $ip = explode(':', $ip)[0];
        }
        
        return $ip ?: '0.0.0.0';
    }

    /**
     * Проверка доступа по паролю (сессия или cookie)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasMaintenanceAccess(Request $request)
    {
        // Проверяем сессию через request (работает после StartSession)
        try {
            if ($request->session()->get('maintenance_access') === true) {
                return true;
            }
        } catch (\Exception $e) {
            // Если сессия еще не доступна, игнорируем
        }
        
        // Проверяем cookie (всегда доступен)
        $cookieHash = hash('sha256', $this->maintenancePassword);
        if ($request->cookie('maintenance_access') === $cookieHash) {
            return true;
        }
        
        return false;
    }

    /**
     * Handle an incoming request.
     * Блокирует все запросы и показывает страницу техработ
     * Исключение: разрешенные IP адреса и пользователи с паролем
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Получаем реальный IP адрес клиента
        $clientIp = $this->getRealIp($request);
        
        // Проверяем, если IP в списке разрешенных - пропускаем
        if (in_array($clientIp, $this->allowedIps)) {
            return $next($request);
        }

        // Проверяем доступ по паролю - ЕСЛИ ЕСТЬ ПАРОЛЬ, ПРОПУСКАЕМ БЕЗ ТЕХРАБОТ
        if ($this->hasMaintenanceAccess($request)) {
            return $next($request);
        }

        // Исключения для API callback'ов, авторизации и socket.io
        $path = $request->path();
        
        // Проверяем префиксы в первую очередь (более общие исключения)
        if (str_starts_with($path, 'api')) {
            return $next($request);
        }
        
        if (str_starts_with($path, 'callback')) {
            return $next($request);
        }
        
        if (str_starts_with($path, 'payment')) {
            return $next($request);
        }
        
        // Точные пути
        $exactExceptions = [
            '/maintenance/verify',
            '/auth/login',
            '/auth/register',
            '/auth/vkontakte',
            '/auth/vkontakte/handle',
            '/user/init',
            '/socket.io',
        ];
        
        foreach ($exactExceptions as $exception) {
            if ($request->is($exception)) {
                return $next($request);
            }
        }

        // Для всех остальных запросов показываем страницу техработ
        return response()->view('maintenance', [], 503);
    }
}

