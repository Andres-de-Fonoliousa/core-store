<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'users' => ['table' => 'users', 'nullable' => false],
        'products' => ['table' => 'products', 'nullable' => false],
        'categories' => ['table' => 'categories', 'nullable' => false],
        'orders' => ['table' => 'orders', 'nullable' => false],
        'transactions' => ['table' => 'transactions', 'nullable' => false],
        'providers' => ['table' => 'providers', 'nullable' => true],
        'notifications' => ['table' => 'notifications', 'nullable' => true],
    ];

    public function up(): void
    {
        foreach ($this->tables as $config) {
            Schema::table($config['table'], function (Blueprint $table) use ($config) {
                if (!Schema::hasColumn($config['table'], 'tenant_id')) {
                    $col = $table->foreignId('tenant_id');
                    if ($config['nullable']) {
                        $col->nullable();
                    }
                    $col->constrained()->cascadeOnDelete();

                    $table->index('tenant_id');
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $config) {
            Schema::table($config['table'], function (Blueprint $table) use ($config) {
                $table->dropForeign([$config['table'] . '_tenant_id_foreign']);
                $table->dropColumn('tenant_id');
            });
        }
    }
};
