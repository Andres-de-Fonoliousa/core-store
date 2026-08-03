<?php

namespace App\Services\Tenant;

use App\Models\Tenant;
use Illuminate\Support\Facades\Cache;

class CatalogCache
{
    public function __construct(
        private TenantManager $manager,
    ) {}

    public function version(): int
    {
        $scope = $this->manager->getCurrentId() ?? 'global';

        return (int) Cache::rememberForever("cat_idx_v:{$scope}", fn () => 1);
    }

    public function key(string $status, string $parentId, int $page, int $perPage): string
    {
        $scope = $this->manager->getCurrentId() ?? 'global';

        return "cat_idx:{$scope}:{$this->version()}:{$status}:{$parentId}:{$page}:{$perPage}";
    }

    public function bust(): void
    {
        $scope = $this->manager->getCurrentId() ?? 'global';

        Cache::increment("cat_idx_v:{$scope}");
    }

    public function bustAll(): void
    {
        Cache::increment('cat_idx_v:global');

        foreach (Tenant::query()->pluck('id') as $tenantId) {
            Cache::increment("cat_idx_v:{$tenantId}");
        }
    }
}
