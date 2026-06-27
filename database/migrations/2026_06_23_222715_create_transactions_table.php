<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('order_id')->nullable(); // FK added in a later migration
    $table->decimal('amount', 12, 2);
    $table->string('type'); // purchase, deposit, refund
    $table->string('status')->default('pending'); // pending, completed, rejected
    $table->decimal('balance_before', 12, 2)->nullable();
    $table->decimal('balance_after', 12, 2)->nullable();
    $table->string('payment_id')->nullable(); // external ref
    $table->timestamp('date')->useCurrent();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
