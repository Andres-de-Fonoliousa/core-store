<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add FK constraints deferred from base migrations (circular dependency)
        // Drop first if it exists (may have auto-named FK from foreignId or none at all)
        if (DB::getDriverName() !== 'sqlite') {
            $fks = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'orders' AND REFERENCED_TABLE_NAME IS NOT NULL AND COLUMN_NAME = 'transaction_id'", [DB::getDatabaseName()]);
            foreach ($fks as $fk) {
                Schema::table('orders', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                });
            }
        }
        Schema::table('orders', function (Blueprint $table) {
            $table->foreign('transaction_id', 'orders_txn_fk')->references('id')->on('transactions');
        });

        if (DB::getDriverName() !== 'sqlite') {
            $fks = DB::select("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'transactions' AND REFERENCED_TABLE_NAME IS NOT NULL AND COLUMN_NAME = 'order_id'", [DB::getDatabaseName()]);
            foreach ($fks as $fk) {
                Schema::table('transactions', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk->CONSTRAINT_NAME);
                });
            }
        }
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('order_id', 'transactions_order_fk')->references('id')->on('orders')->cascadeOnDelete();
        });

        // Add fail_reason column if not already present
        if (!Schema::hasColumn('orders', 'fail_reason')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->text('fail_reason')->nullable()->after('serial_code');
            });
        }

        // Fix existing products (use QB directly to avoid SoftDeletes scope before column exists)
        DB::table('products')->where('is_auto', false)->update(['is_auto' => true]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('orders_txn_fk');
            $table->dropColumn('fail_reason');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('transactions_order_fk');
        });
    }
};
