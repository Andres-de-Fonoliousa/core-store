<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(
        private TenantManager $manager,
        private TenantResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolver->resolve($request);

        if ($tenant && $tenant->status !== 'suspended') {
            $this->manager->setCurrent($tenant);
            $this->applyTenantConfig($tenant);
        } else {
            $this->manager->setPlatformMode();
        }

        $response = $next($request);

        if ($tenant) {
            $response->headers->set('X-Tenant-ID', $tenant->uuid);
        }

        return $response;
    }

    private function applyTenantConfig(Tenant $tenant): void
    {
        config([
            'app.name' => $tenant->name,
            'app.locale' => $tenant->locale,
        ]);

        app()->setLocale($tenant->locale);
    }
}
