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
            $table->string('model_3d_path')->nullable()->after('image_path');
            $table->string('model_3d_usdz_path')->nullable()->after('model_3d_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umkm_product_categories', function (Blueprint $table) {
            $table->dropColumn(['model_3d_path', 'model_3d_usdz_path']);
        });
    }
};
