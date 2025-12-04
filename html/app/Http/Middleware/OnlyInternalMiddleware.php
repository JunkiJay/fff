<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class OnlyInternalMiddleware
{
    /**
     * @param Request $request
     */
    public function handle($request, \Closure $next, $guard = null)
    {
        if ($request->user() && $request->user()->only_internal === 1 && !$request->hasHeader('InternalRequest')) {
            return throw new \Exception('Error');
        }

        return $next($request);
    }
}