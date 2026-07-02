<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create default tenant if it doesn't exist yet
        $exists = DB::table('tenants')->where('slug', 'default')->exists();
        if (!$exists) {
            DB::table('tenants')->insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Default Store',
                'slug' => 'default',
                'subdomain' => 'store',
                'status' => 'active',
                'plan' => 'enterprise',
                'brand_color' => '#22d3ee',
                'locale' => 'ar',
                'currency' => 'USD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $defaultTenantId = DB::table('tenants')->where('slug', 'default')->value('id');

        $tables = ['users', 'products', 'categories', 'orders', 'transactions'];

        // Backfill existing data
        foreach ($tables as $table) {
            DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $defaultTenantId]);
        }

        // Link existing admin users as tenant owners
        $admins = DB::table('users')->where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $exists = DB::table('tenant_user')
                ->where('tenant_id', $defaultTenantId)
                ->where('user_id', $admin->id)
                ->exists();

            if (!$exists) {
                DB::table('tenant_user')->insert([
                    'tenant_id' => $defaultTenantId,
                    'user_id' => $admin->id,
                    'role' => 'owner',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Add FK constraints + make NOT NULL on required tables
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->unsignedBigInteger('tenant_id')->nullable(false)->change();
                $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }

        // FK for nullable tables
        foreach (['providers', 'notifications'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['users', 'products', 'categories', 'orders', 'transactions', 'providers', 'notifications'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign([$table . '_tenant_id_foreign']);
            });
        }
    }
};
