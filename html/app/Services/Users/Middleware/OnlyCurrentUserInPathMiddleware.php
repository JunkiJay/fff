<?php

declare(strict_types=1);

namespace App\Services\Users\Middleware;

use App\Exceptions\ForbiddenHttpException;
use Illuminate\Http\Request;

class OnlyCurrentUserInPathMiddleware
{
    public function handle(Request $request, callable $next)
    {
        $inPath = $request->route('userId');

        if ($request->user()?->id !== (int) $inPath) {
            throw new ForbiddenHttpException();
        }

        return $next($request);
    }
}