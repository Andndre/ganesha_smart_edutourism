<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_route_points', function (Blueprint $table) {
            // Fallback QR for points whose locationable has no AR marker
            // (points WITH an AR marker reuse that marker's QR — one sticker per location).
            $table->string('qr_code_token')->nullable()->unique()->after('storytelling_content');
        });
    }

    public function down(): void
    {
        Schema::table('tour_route_points', function (Blueprint $table) {
            $table->dropColumn('qr_code_token');
        });
    }
};
