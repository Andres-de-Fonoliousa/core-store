<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['users', 'products', 'categories', 'orders', 'transactions', 'providers', 'notifications'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'tenant_id')) {
                    $t->unsignedBigInteger('tenant_id')->nullable()->after('id');
                    $t->index('tenant_id');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = ['users', 'products', 'categories', 'orders', 'transactions', 'providers', 'notifications'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropIndex(['tenant_id']);
                $t->dropColumn('tenant_id');
            });
        }
    }
};
