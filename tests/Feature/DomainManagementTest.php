<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\Tenant\DomainVerifier;
use App\Services\Tenant\TenantResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DomainManagementTest extends TestCase
{
    use RefreshDatabase;

    private function authFor(Tenant $tenant, User $user): DomainManagementTest
    {
        return $this->actingAs($user)->withHeader('X-Tenant-ID', $tenant->uuid);
    }

    public function test_free_plan_cannot_add_custom_domain(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'free']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);

        $this->authFor($tenant, $user)
            ->postJson('/api/admin/domains', ['domain' => 'shop.example.com'])
            ->assertForbidden();
    }

    public function test_tenant_can_add_and_list_custom_domains(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'pro']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);

        $this->authFor($tenant, $user)
            ->postJson('/api/admin/domains', ['domain' => 'shop.example.com'])
            ->assertCreated()
            ->assertJsonPath('domain.domain', 'shop.example.com');

        $this->assertNotNull($tenant->domainAliases()->first()->verification_token);
        $this->assertNull($tenant->domainAliases()->first()->verified_at);

        $this->authFor($tenant, $user)
            ->getJson('/api/admin/domains')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_duplicate_domain_is_rejected(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'pro']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);

        $this->authFor($tenant, $user)->postJson('/api/admin/domains', ['domain' => 'shop.example.com'])->assertCreated();

        $this->authFor($tenant, $user)
            ->postJson('/api/admin/domains', ['domain' => 'shop.example.com'])
            ->assertStatus(422);
    }

    public function test_primary_domain_of_another_tenant_is_rejected(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'pro', 'domain' => 'taken.com']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);

        $this->authFor($tenant, $user)
            ->postJson('/api/admin/domains', ['domain' => 'taken.com'])
            ->assertStatus(409);
    }

    public function test_verify_sets_verified_at_when_dns_record_matches(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'pro']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);
        $alias = $tenant->domainAliases()->create([
            'domain' => 'shop.example.com',
            'verification_token' => 'token123',
        ]);

        $this->mock(DomainVerifier::class)->shouldReceive('verify')->once()->andReturn(true);

        $this->authFor($tenant, $user)
            ->postJson("/api/admin/domains/{$alias->id}/verify")
            ->assertOk();

        $this->assertNotNull($alias->fresh()->verified_at);
    }

    public function test_verify_fails_when_dns_record_does_not_match(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'pro']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);
        $alias = $tenant->domainAliases()->create([
            'domain' => 'shop.example.com',
            'verification_token' => 'token123',
        ]);

        $this->mock(DomainVerifier::class)->shouldReceive('verify')->once()->andReturn(false);

        $this->authFor($tenant, $user)
            ->postJson("/api/admin/domains/{$alias->id}/verify")
            ->assertStatus(422);

        $this->assertNull($alias->fresh()->verified_at);
    }

    public function test_tenant_cannot_manage_other_tenants_domains(): void
    {
        $tenantA = Tenant::factory()->create(['plan' => 'pro']);
        $tenantB = Tenant::factory()->create(['plan' => 'pro']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenantA->id]);
        $alias = $tenantB->domainAliases()->create([
            'domain' => 'other.example.com',
            'verification_token' => 'token123',
        ]);

        $this->authFor($tenantA, $user)
            ->postJson("/api/admin/domains/{$alias->id}/verify")
            ->assertNotFound();

        $this->authFor($tenantA, $user)
            ->deleteJson("/api/admin/domains/{$alias->id}")
            ->assertNotFound();
    }

    public function test_tenant_can_remove_own_domain(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'pro']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);
        $alias = $tenant->domainAliases()->create([
            'domain' => 'shop.example.com',
            'verification_token' => 'token123',
        ]);

        $this->authFor($tenant, $user)
            ->deleteJson("/api/admin/domains/{$alias->id}")
            ->assertOk();

        $this->assertDatabaseMissing('domain_aliases', ['id' => $alias->id]);
    }

    public function test_resolver_uses_verified_custom_domain(): void
    {
        $tenant = Tenant::factory()->create();
        $tenant->domainAliases()->create([
            'domain' => 'shop.example.com',
            'verification_token' => 'token123',
            'verified_at' => now(),
        ]);

        $request = Request::create('http://shop.example.com/');

        $this->assertSame(
            $tenant->id,
            $this->app->make(TenantResolver::class)->resolve($request)->id
        );
    }

    public function test_resolver_ignores_unverified_custom_domain(): void
    {
        $tenant = Tenant::factory()->create();
        $tenant->domainAliases()->create([
            'domain' => 'shop.example.com',
            'verification_token' => 'token123',
        ]);

        $request = Request::create('http://shop.example.com/');

        $this->assertNull($this->app->make(TenantResolver::class)->resolve($request));
    }
}
