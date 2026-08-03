<?php

namespace App\Http\Middleware;

use App\Services\Tenant\TenantManager;
use Closure;
use Illuminate\Http\Request;

class PlatformAdminMiddleware
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        if (! $request->user() || ! $request->user()->is_platform_admin) {
            abort(403, 'Unauthorized. Platform admin access required.');
        }

        $this->manager->setPlatformMode();

        return $next($request);
    }
}
