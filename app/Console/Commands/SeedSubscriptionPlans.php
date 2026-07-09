<?php

namespace App\Console\Commands;

use App\Models\SubscriptionPlan;
use Illuminate\Console\Command;

class SeedSubscriptionPlans extends Command
{
    protected $signature = 'plans:seed';
    protected $description = 'Seed default subscription plans';

    public function handle(): int
    {
        $plans = [
            [
                'name' => 'Free',
                'code' => 'free',
                'price_monthly' => 0,
                'transaction_fee' => 0,
                'limits' => [
                    'max_products' => 10,
                    'max_users' => 3,
                    'max_categories' => 5,
                ],
                'features' => [
                    'custom_domain' => false,
                    'auto_fulfillment' => false,
                    'api_access' => false,
                    'priority_support' => false,
                    'team_management' => false,
                    'analytics' => false,
                ],
            ],
            [
                'name' => 'Pro',
                'code' => 'pro',
                'price_monthly' => 29,
                'transaction_fee' => 0.02,
                'limits' => [
                    'max_products' => 500,
                    'max_users' => 25,
                    'max_categories' => 50,
                ],
                'features' => [
                    'custom_domain' => true,
                    'auto_fulfillment' => true,
                    'api_access' => false,
                    'priority_support' => false,
                    'team_management' => true,
                    'analytics' => true,
                ],
            ],
            [
                'name' => 'Enterprise',
                'code' => 'enterprise',
                'price_monthly' => 99,
                'transaction_fee' => 0.015,
                'limits' => [
                    'max_products' => -1,
                    'max_users' => -1,
                    'max_categories' => -1,
                ],
                'features' => [
                    'custom_domain' => true,
                    'auto_fulfillment' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'team_management' => true,
                    'analytics' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['code' => $plan['code']],
                $plan,
            );
            $this->info("Seeded plan: {$plan['name']}");
        }

        return Command::SUCCESS;
    }
}
