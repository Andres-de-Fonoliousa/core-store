<?php

namespace App\Console\Commands;

use App\Models\SubscriptionInvoice;
use App\Models\Tenant;
use App\Services\Tenant\PlanFeatures;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature = 'billing:process';
    protected $description = 'Process monthly subscription deductions for all active tenants';

    public function handle(): int
    {
        $today = now()->startOfDay();

        Tenant::whereIn('status', ['active', 'trial'])
            ->where('trial_ends_at', '<', now())
            ->each(function (Tenant $tenant) use ($today) {
                $this->processTenant($tenant, $today);
            });

        return Command::SUCCESS;
    }

    private function processTenant(Tenant $tenant, \Illuminate\Support\Carbon $today): void
    {
        $price = PlanFeatures::price($tenant->plan);

        if ($price <= 0) {
            return;
        }

        $periodEnd = $today->copy()->addMonth();

        if ($price > 0 && $tenant->platform_balance < $price) {
            $tenant->update(['status' => 'suspended']);
            $this->warn("Tenant {$tenant->id} ({$tenant->name}) suspended — insufficient balance");
            return;
        }

        if ($price > 0) {
            $tenant->decrement('platform_balance', $price);
        }

        SubscriptionInvoice::create([
            'tenant_id' => $tenant->id,
            'plan_code' => $tenant->plan,
            'amount' => $price,
            'period_start' => $today,
            'period_end' => $periodEnd,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $tenant->update([
            'expires_at' => $periodEnd,
            'subscribed_at' => $tenant->subscribed_at ?? now(),
        ]);

        $this->info("Billed tenant {$tenant->id} ({$tenant->name}): \${$price}");
    }
}
