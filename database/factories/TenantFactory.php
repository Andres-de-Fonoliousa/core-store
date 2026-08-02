<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $slug = $this->faker->unique()->slug(2);

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $this->faker->company(),
            'slug' => $slug,
            'subdomain' => $slug,
            'status' => 'trial',
            'plan' => 'free',
            'locale' => 'en',
            'currency' => 'USD',
            'brand_color' => '#22d3ee',
            'brand_color_dark' => '#06b6d4',
            'trial_ends_at' => now()->addDays(14),
        ];
    }
}
