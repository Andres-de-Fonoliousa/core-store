<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class TenantCache
{
    public function remember(Tenant $tenant, string $key, int $ttl, callable $callback): mixed
    {
        $fullKey = $this->key($tenant, $key);
        $this->track($tenant, $fullKey);

        return Cache::remember($fullKey, $ttl, $callback);
    }

    public function flush(Tenant $tenant): void
    {
        $indexKey = $this->key($tenant, '__keys');
        $keys = Cache::get($indexKey, []);

        foreach ($keys as $fullKey) {
            Cache::forget($fullKey);
        }

        Cache::forget($indexKey);
    }

    private function track(Tenant $tenant, string $fullKey): void
    {
        $indexKey = $this->key($tenant, '__keys');
        $keys = Cache::get($indexKey, []);

        if (! in_array($fullKey, $keys, true)) {
            $keys[] = $fullKey;
            Cache::put($indexKey, $keys, now()->addDays(7));
        }
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
