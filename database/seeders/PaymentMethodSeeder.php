<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::upsert([
            ['code' => 'manual_screenshot', 'name' => 'تحويل بنكي / إيداع يدوي', 'is_active' => true, 'sort' => 1],
            ['code' => 'sham_cash', 'name' => 'Sham Cash (آلي)', 'is_active' => true, 'sort' => 2],
        ], ['code'], ['name', 'is_active', 'sort']);
    }
}
