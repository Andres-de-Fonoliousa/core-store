<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PlatformAdminMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!$request->user() || !$request->user()->is_platform_admin) {
            abort(403, 'Unauthorized. Platform admin access required.');
        }

        return $next($request);
    }
}
