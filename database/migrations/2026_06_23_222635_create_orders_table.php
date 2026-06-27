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
        Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('product_id')->constrained();
    $table->decimal('price_at_time_of_order', 12, 2);
    $table->unsignedInteger('quantity'); // the selected qty from product's qty_values
    $table->string('status')->default('pending_payment'); // pending_payment, paid, fulfilled, cancelled
    $table->json('details')->nullable(); // for payment proof file path, user comments, etc.
    $table->foreignId('transaction_id')->nullable(); // FK added in a later migration
    $table->text('serial_code')->nullable(); // encrypted code
    $table->string('fulfillment_status')->default('pending');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
