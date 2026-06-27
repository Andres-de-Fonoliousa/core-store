<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sham_cash_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tran_id')->unique();
            $table->decimal('amount', 12, 2);
            $table->unsignedSmallInteger('currency_id');
            $table->string('currency_name', 10);
            $table->string('sender_name')->nullable();
            $table->string('sender_account')->nullable();
            $table->string('sender_address')->nullable();
            $table->string('note')->nullable();
            $table->date('tran_date');
            $table->time('tran_time');
            $table->json('raw')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sham_cash_payments');
    }
};
