<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umkm_product_categories', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('description');
            $table->string('unit', 50)->nullable()->after('price');
        });

        Schema::table('umkm_products', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('slug')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->decimal('price', 12, 2)->nullable()->change();
            $table->string('unit', 50)->nullable()->change();
            $table->json('images')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('umkm_product_categories', function (Blueprint $table) {
            $table->dropColumn(['price', 'unit']);
        });
        // umkm_products columns are left nullable on rollback — original schema was
        // already mixed; reverting nullability isn't safe without seeded defaults.
    }
};
