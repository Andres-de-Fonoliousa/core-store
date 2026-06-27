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
        Schema::create('providers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('base_url');
    $table->text('token'); // consider encrypting it, but store as text
    $table->string('image')->nullable();
    $table->boolean('sync_active')->default(false);
    $table->string('status')->default('active');
    $table->decimal('balance', 12, 2)->default(0.00);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
