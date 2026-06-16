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
            $table->decimal('latitude', 10, 8)->nullable()->after('zone_identifier');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('radius_meters')->default(50)->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capacity_zones', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'radius_meters']);
        });
    }
};
