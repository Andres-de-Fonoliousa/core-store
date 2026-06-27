<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Categories: status filter + parent tree navigation
        Schema::table('categories', function (Blueprint $table) {
            $table->index('status');
            $table->index(['parent_id', 'status']);
        });

        // Products: browse by active status + category, sync lookup
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'category_id']);
            $table->index(['external_id', 'provider_id']);
        });

        // Orders: customer order list, admin filtering
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index('status');
            $table->index('fulfillment_status');
        });

        // Transactions: admin deposit filtering, customer list
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['type', 'status', 'created_at']);
        });

        // Users: admin check, login lookup
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
        });

        // Notifications: customer notification filtering by type
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['type', 'notifiable_id']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['parent_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status', 'category_id']);
            $table->dropIndex(['external_id', 'provider_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['fulfillment_status']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['type', 'status', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['type', 'notifiable_id']);
        });
    }
};
