<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Notifications\WelcomeOnboarding;
use Illuminate\Console\Command;

class SendOnboardingFollowUp extends Command
{
    protected $signature = 'onboarding:follow-up';
    protected $description = 'Send follow-up emails to tenants who have not completed onboarding';

    public function handle(): int
    {
        $cutoff = now()->subHours(24);

        $tenants = Tenant::whereNull('onboarding_completed_at')
            ->where('created_at', '<=', $cutoff)
            ->where('created_at', '>=', now()->subDays(3))
            ->get();

        $sent = 0;
        foreach ($tenants as $tenant) {
            $owner = $tenant->users()->wherePivot('role', 'owner')->first();
            if ($owner) {
                $owner->notify(new WelcomeOnboarding($tenant));
                $sent++;
            }
        }

        $this->info("Sent {$sent} onboarding follow-up emails.");

        return self::SUCCESS;
    }
}
