<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\Admin\KpiController;
use App\Models\Tenant;
use App\Services\Tenant\CatalogCache;
use App\Services\Tenant\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TenantCacheIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_cache_keys_are_tenant_scoped(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        app(TenantManager::class)->setCurrent($tenantA);
        $cache = app(CatalogCache::class);
        $keyA = $cache->key('active', 'null', 1, 20);

        Cache::put($keyA, 'data-a', 60);

        app(TenantManager::class)->setCurrent($tenantB);
        $cacheB = app(CatalogCache::class);
        $keyB = $cacheB->key('active', 'null', 1, 20);

        $this->assertNotSame($keyA, $keyB);
        $this->assertNull(Cache::get($keyB));
        $this->assertSame('data-a', Cache::get($keyA));
    }

    public function test_bust_increments_current_tenant_version_only(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        app(TenantManager::class)->setCurrent($tenantA);
        $cache = app(CatalogCache::class);
        $versionBefore = $cache->version();

        $cache->bust();

        $versionA = $cache->version();
        $this->assertSame($versionBefore + 1, $versionA);

        app(TenantManager::class)->setCurrent($tenantB);
        $this->assertSame($versionBefore, app(CatalogCache::class)->version());
    }

    public function test_kpi_cache_is_tenant_scoped(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        app(TenantManager::class)->setCurrent($tenantA);
        app(KpiController::class)->index();
        $this->assertTrue(Cache::has("admin:kpi:{$tenantA->id}"));

        app(TenantManager::class)->setCurrent($tenantB);
        $this->assertFalse(Cache::has("admin:kpi:{$tenantB->id}"));
    }
}
