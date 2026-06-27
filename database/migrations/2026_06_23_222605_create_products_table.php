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
        Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->foreignId('provider_id')->constrained();
    $table->foreignId('category_id')->constrained();
    $table->string('name');
    $table->decimal('price', 12, 2);
    $table->decimal('cost_price', 12, 2);
    $table->string('external_id');
    $table->string('image')->nullable();
    $table->json('params')->nullable();
    $table->json('qty_values'); // e.g. [50,100,200]
    $table->boolean('is_auto')->default(false);
    $table->string('status')->default('active');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
