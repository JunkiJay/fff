<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\RedirectResponse;

class Access
{
    protected $auth;
    protected $token;

    public function __construct()
    {
        $this->auth = Auth::User();
    }

    public function handle($request, Closure $next, $role)
    {
        if($this->auth) {
            switch($role){
                case 'admin':
                    if(!$this->auth->is_admin) {
                        if($this->auth->is_promocoder) return new RedirectResponse(url('/admin/promocodes'));
                        return new RedirectResponse(url('/'));
                    }
                break;
                default:
                    return new RedirectResponse(url('/'));
                break;
            }
            return $next($request);
        }
        return new RedirectResponse(url('/'));
    }
}
