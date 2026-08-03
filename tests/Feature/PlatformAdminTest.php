<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenant\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_middleware_forces_platform_mode(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $admin = User::factory()->create([
            'is_platform_admin' => true,
            'tenant_id' => $tenantA->id,
        ]);

        app(TenantManager::class)->setCurrent($tenantA);

        $this->actingAs($admin)
            ->getJson('/api/platform/admin/dashboard')
            ->assertOk();

        $this->assertTrue(app(TenantManager::class)->isPlatformRequest());
    }

    public function test_non_platform_admin_is_rejected(): void
    {
        $regularUser = User::factory()->create();

        $this->actingAs($regularUser)
            ->getJson('/api/platform/admin/dashboard')
            ->assertForbidden();
    }

    public function test_grant_platform_admin_command(): void
    {
        $user = User::factory()->create();

        $this->artisan('platform:grant-admin', ['email' => $user->email])
            ->assertExitCode(0);

        $this->assertTrue($user->refresh()->is_platform_admin);

        $this->artisan('platform:grant-admin', ['email' => $user->email, '--revoke' => true])
            ->assertExitCode(0);

        $this->assertFalse($user->refresh()->is_platform_admin);
    }
}
