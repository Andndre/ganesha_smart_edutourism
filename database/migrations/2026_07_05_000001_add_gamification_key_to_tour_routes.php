<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_routes', function (Blueprint $table) {
            // Stable machine key for gamification (badge/collectible/avatar) so the
            // logic no longer depends on the admin-editable display name.
            $table->string('gamification_key')->nullable()->after('difficulty');
        });

        // Backfill the 3 seeded routes by their current name so existing data
        // keeps working without a reseed.
        $map = [
            'Heritage Quest' => 'heritage_quest',
            'Cultural Adventure' => 'cultural_adventure',
            'Eco Quest' => 'eco_quest',
        ];

        foreach ($map as $needle => $key) {
            DB::table('tour_routes')
                ->where('name->en', 'like', "%{$needle}%")
                ->update(['gamification_key' => $key]);
        }
    }

    public function down(): void
    {
        Schema::table('tour_routes', function (Blueprint $table) {
            $table->dropColumn('gamification_key');
        });
    }
};
