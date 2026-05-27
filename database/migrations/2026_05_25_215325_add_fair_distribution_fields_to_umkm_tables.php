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
        Schema::table('umkm_product_categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
            $table->string('image_path')->nullable()->after('description');
            $table->string('icon')->nullable()->after('image_path');
        });

        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->unsignedInteger('recommendation_count')->default(0)->after('is_active');
            $table->boolean('accepts_in_app_payment')->default(false)->after('recommendation_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umkm_product_categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'image_path', 'icon']);
        });

        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->dropColumn(['recommendation_count', 'accepts_in_app_payment']);
        });
    }
};
