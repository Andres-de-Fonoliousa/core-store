<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantAutoAssignSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_hook_assigns_current_tenant(): void
    {
        $tenant = Tenant::factory()->create();

        app(TenantManager::class)->setCurrent($tenant);

        $provider = Provider::create(['name' => 'P', 'code' => 'P', 'base_url' => 'https://example.test', 'token' => 'x', 'sync_active' => false]);
        $category = Category::create(['name' => 'C', 'status' => 'active']);

        $product = Product::create([
            'provider_id' => $provider->id,
            'category_id' => $category->id,
            'name' => 'Smoke',
            'cost_price' => 10,
            'price' => 10,
            'status' => 'active',
            'qty_values' => [1],
            'external_id' => 'smoke-1',
        ]);

        $this->assertSame($tenant->id, $product->tenant_id);
    }
}
