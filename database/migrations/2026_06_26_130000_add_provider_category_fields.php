<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('provider_category_id')->nullable()->after('id');
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete()->after('provider_category_id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['provider_category_id', 'parent_id']);
        });
    }
};
