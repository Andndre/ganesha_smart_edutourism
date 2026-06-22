<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite requires dropping the index before the column
        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->dropIndex('umkm_profiles_category_index');
        });

        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->dropColumn(['category', 'accepts_in_app_payment']);
        });

        Schema::table('umkm_products', function (Blueprint $table) {
            $table->dropColumn('ar_model_path');
        });
    }

    public function down(): void
    {
        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->string('category')->nullable();
            $table->boolean('accepts_in_app_payment')->default(false);
            $table->index('category');
        });

        Schema::table('umkm_products', function (Blueprint $table) {
            $table->string('ar_model_path')->nullable();
        });
    }
};
