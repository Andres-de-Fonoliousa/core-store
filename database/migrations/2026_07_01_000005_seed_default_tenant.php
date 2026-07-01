<?php

use App\Models\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Tenant::where('slug', 'default')->exists()) {
            return;
        }

        DB::transaction(function () {
            $tenant = Tenant::create([
                'name' => 'Default Store',
                'slug' => 'default',
                'subdomain' => 'store',
                'status' => 'active',
                'plan' => 'enterprise',
                'brand_color' => '#22d3ee',
                'locale' => 'ar',
                'currency' => 'USD',
            ]);

            // Backfill tenant_id for existing data
            $tables = ['users', 'products', 'categories', 'orders', 'transactions', 'providers'];
            foreach ($tables as $table) {
                if (Schema::hasColumn($table, 'tenant_id')) {
                    DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
                }
            }

            // Link existing admin users as tenant owners
            $admins = DB::table('users')->where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $exists = DB::table('tenant_user')
                    ->where('tenant_id', $tenant->id)
                    ->where('user_id', $admin->id)
                    ->exists();

                if (!$exists) {
                    DB::table('tenant_user')->insert([
                        'tenant_id' => $tenant->id,
                        'user_id' => $admin->id,
                        'role' => 'owner',
                        'joined_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        $tenant = Tenant::where('slug', 'default')->first();
        if ($tenant) {
            $tables = ['users', 'products', 'categories', 'orders', 'transactions', 'providers'];
            foreach ($tables as $table) {
                if (Schema::hasColumn($table, 'tenant_id')) {
                    DB::table($table)->where('tenant_id', $tenant->id)->update(['tenant_id' => null]);
                }
            }
            $tenant->delete();
        }
    }
};
