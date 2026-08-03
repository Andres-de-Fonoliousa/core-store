<?php

namespace Tests\Feature;

use App\Models\SubscriptionInvoice;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function authFor(Tenant $tenant, User $user): BillingTest
    {
        return $this->actingAs($user)->withHeader('X-Tenant-ID', $tenant->uuid);
    }

    public function test_active_tenant_without_trial_is_billed(): void
    {
        $tenant = Tenant::factory()->create([
            'status' => 'active',
            'plan' => 'pro',
            'trial_ends_at' => null,
            'platform_balance' => 100,
        ]);

        $this->artisan('billing:process')->assertExitCode(0);

        $invoice = SubscriptionInvoice::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(29, $invoice->amount);
        $this->assertEquals('paid', $invoice->status);

        $this->assertEquals(71.0, $tenant->fresh()->platform_balance);
        $this->assertEquals('active', $tenant->fresh()->status);
        $this->assertNotNull($tenant->fresh()->expires_at);
    }

    public function test_trial_tenant_is_billed_after_trial_ends_and_flips_to_active(): void
    {
        $tenant = Tenant::factory()->create([
            'status' => 'trial',
            'plan' => 'pro',
            'trial_ends_at' => now()->subDay(),
            'platform_balance' => 100,
        ]);

        $this->artisan('billing:process')->assertExitCode(0);

        $this->assertEquals(1, SubscriptionInvoice::where('tenant_id', $tenant->id)->count());
        $this->assertEquals('active', $tenant->fresh()->status);
    }

    public function test_trial_tenant_before_trial_end_is_not_billed(): void
    {
        $tenant = Tenant::factory()->create([
            'status' => 'trial',
            'plan' => 'pro',
            'trial_ends_at' => now()->addDays(10),
            'platform_balance' => 100,
        ]);

        $this->artisan('billing:process')->assertExitCode(0);

        $this->assertEquals(0, SubscriptionInvoice::where('tenant_id', $tenant->id)->count());
        $this->assertEquals('trial', $tenant->fresh()->status);
    }

    public function test_insufficient_balance_suspends_tenant(): void
    {
        $tenant = Tenant::factory()->create([
            'status' => 'active',
            'plan' => 'pro',
            'trial_ends_at' => null,
            'platform_balance' => 10,
        ]);

        $this->artisan('billing:process')->assertExitCode(0);

        $this->assertEquals('suspended', $tenant->fresh()->status);
        $this->assertEquals(0, SubscriptionInvoice::where('tenant_id', $tenant->id)->count());
    }

    public function test_billing_is_idempotent(): void
    {
        $tenant = Tenant::factory()->create([
            'status' => 'active',
            'plan' => 'pro',
            'trial_ends_at' => null,
            'platform_balance' => 100,
        ]);

        $this->artisan('billing:process')->assertExitCode(0);
        $this->artisan('billing:process')->assertExitCode(0);

        $this->assertEquals(1, SubscriptionInvoice::where('tenant_id', $tenant->id)->count());
        $this->assertEquals(71.0, $tenant->fresh()->platform_balance);
    }

    public function test_free_plan_is_never_billed(): void
    {
        $tenant = Tenant::factory()->create([
            'status' => 'active',
            'plan' => 'free',
            'trial_ends_at' => null,
            'platform_balance' => 100,
        ]);

        $this->artisan('billing:process')->assertExitCode(0);

        $this->assertEquals(0, SubscriptionInvoice::where('tenant_id', $tenant->id)->count());
        $this->assertEquals(100.0, $tenant->fresh()->platform_balance);
    }

    public function test_overview_returns_plan_and_usage(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'pro', 'platform_balance' => 50]);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);
        $tenant->users()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);

        SubscriptionInvoice::create([
            'tenant_id' => $tenant->id,
            'plan_code' => 'pro',
            'amount' => 29,
            'period_start' => now()->subMonth(),
            'period_end' => now(),
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->authFor($tenant, $user)
            ->getJson('/api/admin/billing/overview')
            ->assertOk()
            ->assertJsonPath('tenant.plan', 'pro')
            ->assertJsonPath('tenant.platform_balance', 50)
            ->assertJsonPath('plan.price_monthly', 29)
            ->assertJsonPath('plan.transaction_fee', 0.02)
            ->assertJsonPath('usage.users', 1)
            ->assertJsonPath('usage.products', 0);
    }

    public function test_invoices_are_listed(): void
    {
        $tenant = Tenant::factory()->create();
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);

        SubscriptionInvoice::create([
            'tenant_id' => $tenant->id,
            'plan_code' => 'pro',
            'amount' => 29,
            'period_start' => now()->subMonth(),
            'period_end' => now(),
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->authFor($tenant, $user)
            ->getJson('/api/admin/billing/invoices')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_plan_can_be_changed(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'free']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);

        $this->authFor($tenant, $user)
            ->postJson('/api/admin/billing/plan', ['plan' => 'pro'])
            ->assertOk();

        $this->assertEquals('pro', $tenant->fresh()->plan);
    }

    public function test_plan_change_rejects_invalid_plan(): void
    {
        $tenant = Tenant::factory()->create(['plan' => 'free']);
        $user = User::factory()->create(['role' => 'admin', 'tenant_id' => $tenant->id]);

        $this->authFor($tenant, $user)
            ->postJson('/api/admin/billing/plan', ['plan' => 'mega'])
            ->assertStatus(422);

        $this->assertEquals('free', $tenant->fresh()->plan);
    }
}
