<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umkm_product_categories', function (Blueprint $table) {
            $table->boolean('is_culinary')->default(false)->index()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('umkm_product_categories', function (Blueprint $table) {
            $table->dropIndex(['is_culinary']);
            $table->dropColumn('is_culinary');
        });
    }
};
