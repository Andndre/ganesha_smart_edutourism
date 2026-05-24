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
        Schema::create('umkm_product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('umkm_products', function (Blueprint $table) {
            $table->foreignId('umkm_product_category_id')
                ->nullable()
                ->after('umkm_profile_id')
                ->constrained('umkm_product_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umkm_products', function (Blueprint $table) {
            $table->dropForeign(['umkm_product_category_id']);
            $table->dropColumn('umkm_product_category_id');
        });

        Schema::dropIfExists('umkm_product_categories');
    }
};
