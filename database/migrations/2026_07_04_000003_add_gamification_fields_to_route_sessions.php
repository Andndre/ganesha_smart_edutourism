<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            // Generic collectible slugs (e.g. digital_passport, heritage_key_1, eco_crystal_3)
            // so Route 2/3 (Day 4) need no further migration.
            $table->json('collectibles_earned')->nullable()->after('missions_completed');
            $table->string('badge_awarded')->nullable()->after('collectibles_earned');
            $table->string('selected_avatar')->nullable()->after('badge_awarded');
        });
    }

    public function down(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            $table->dropColumn(['collectibles_earned', 'badge_awarded', 'selected_avatar']);
        });
    }
};
