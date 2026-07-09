<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantCache
{
    public function remember(Tenant $tenant, string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($this->key($tenant, $key), $ttl, $callback);
    }

    public function flush(Tenant $tenant): void
    {
        Cache::forget("tenant:{$tenant->id}:*");
    }

    public function key(Tenant $tenant, string $key): string
    {
        return "tenant:{$tenant->id}:{$key}";
    }

    public function userKey(Tenant $tenant, int $userId, string $key): string
    {
        return "tenant:{$tenant->id}:user:{$userId}:{$key}";
    }

    public function get(string $key): mixed
    {
        return Cache::get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        Cache::put($key, $value, $ttl);
    }
}
