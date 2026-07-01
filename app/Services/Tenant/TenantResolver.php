<?php

namespace App\Services\Tenant;

use App\Models\DomainAlias;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantResolver
{
    public function resolve(Request $request): ?Tenant
    {
        $tenant = $this->fromHeader($request);

        if ($tenant) {
            return $tenant;
        }

        $tenant = $this->fromSubdomain($request);

        if ($tenant) {
            return $tenant;
        }

        $tenant = $this->fromCustomDomain($request);

        if ($tenant) {
            return $tenant;
        }

        return null;
    }

    private function fromHeader(Request $request): ?Tenant
    {
        $uuid = $request->header('X-Tenant-ID');

        if (!$uuid) {
            return null;
        }

        return Tenant::where('uuid', $uuid)->first();
    }

    private function fromSubdomain(Request $request): ?Tenant
    {
        $host = $request->getHost();

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $parts = explode('.', $host);

        if (count($parts) < 3) {
            return null;
        }

        $subdomain = $parts[0];

        if (in_array($subdomain, ['www', 'mail', 'admin', 'api', 'test', 'dev', 'app'])) {
            return null;
        }

        return Tenant::where('subdomain', $subdomain)->first();
    }

    private function fromCustomDomain(Request $request): ?Tenant
    {
        $host = $request->getHost();

        $tenant = Tenant::where('domain', $host)->first();

        if ($tenant) {
            return $tenant;
        }

        $alias = DomainAlias::where('domain', $host)
            ->whereNotNull('verified_at')
            ->with('tenant')
            ->first();

        return $alias?->tenant;
    }
}
