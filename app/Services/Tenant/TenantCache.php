<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantCache
{
    public function remember(Tenant $tenant, string $key, int $ttl, callable $callback): mixed
    {
        return Cache::tags(["tenant:{$tenant->id}"])->remember($key, $ttl, $callback);
    }

    public function flush(Tenant $tenant): void
    {
        Cache::tags(["tenant:{$tenant->id}"])->flush();
    }

    public function key(Tenant $tenant, string $key): string
    {
        return "tenant:{$tenant->id}:{$key}";
    }

    public function userKey(Tenant $tenant, int $userId, string $key): string
    {
        return "tenant:{$tenant->id}:user:{$userId}:{$key}";
    }
}
