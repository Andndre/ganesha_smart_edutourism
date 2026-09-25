<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('umkm_product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_product_id')->constrained()->cascadeOnDelete();
            $table->json('label');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['umkm_product_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkm_product_variants');
    }
};
