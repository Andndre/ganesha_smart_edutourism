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
        Schema::create('umkm_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('owner_name');
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category', ['culinary', 'craft', 'souvenir', 'service']);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('ar_marker_id')->unique();
            $table->decimal('rating', 2, 1)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for filtering
            $table->index('category');
            $table->index('is_active');
        });

        Schema::create('umkm_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_profile_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->string('unit')->default('pcs');
            $table->json('images')->nullable();
            $table->string('ar_model_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index for filtering active products
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umkm_products');
        Schema::dropIfExists('umkm_profiles');
    }
};
