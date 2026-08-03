<?php

namespace App\Services\Tenant;

use App\Models\Tenant;

class PlanFeatures
{
    private static array $defaultPlans = [
        'free' => [
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
        'pro' => [
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
        'enterprise' => [
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

    public static function plans(): array
    {
        return array_keys(self::$defaultPlans);
    }

    public static function limits(string $plan): array
    {
        return self::$defaultPlans[$plan]['limits'] ?? self::$defaultPlans['free']['limits'];
    }

    public static function features(string $plan): array
    {
        return self::$defaultPlans[$plan]['features'] ?? self::$defaultPlans['free']['features'];
    }

    public static function price(string $plan): float
    {
        return self::$defaultPlans[$plan]['price_monthly'] ?? 0;
    }

    public static function transactionFee(string $plan): float
    {
        return self::$defaultPlans[$plan]['transaction_fee'] ?? 0;
    }

    public static function hasFeature(Tenant $tenant, string $feature): bool
    {
        $features = self::features($tenant->plan);

        return $features[$feature] ?? false;
    }

    public static function canCreateProduct(Tenant $tenant): bool
    {
        $limits = self::limits($tenant->plan);
        if ($limits['max_products'] === -1) {
            return true;
        }

        return $tenant->products()->count() < $limits['max_products'];
    }

    public static function canInviteUser(Tenant $tenant): bool
    {
        $limits = self::limits($tenant->plan);
        if ($limits['max_users'] === -1) {
            return true;
        }

        return $tenant->users()->count() < $limits['max_users'];
    }

    public static function canCreateCategory(Tenant $tenant): bool
    {
        $limits = self::limits($tenant->plan);
        if ($limits['max_categories'] === -1) {
            return true;
        }

        return $tenant->categories()->count() < $limits['max_categories'];
    }

    public static function apiRateLimit(Tenant $tenant): int
    {
        return match ($tenant->plan) {
            'free' => 30,
            'pro' => 100,
            'enterprise' => 300,
            default => 60,
        };
    }
}
