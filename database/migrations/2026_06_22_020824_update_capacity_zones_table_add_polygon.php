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
        Schema::table('capacity_zones', function (Blueprint $table) {
            $table->json('polygon_coordinates')->nullable()->after('longitude');
            $table->dropColumn('radius_meters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capacity_zones', function (Blueprint $table) {
            $table->integer('radius_meters')->default(50)->after('longitude');
            $table->dropColumn('polygon_coordinates');
        });
    }
};
