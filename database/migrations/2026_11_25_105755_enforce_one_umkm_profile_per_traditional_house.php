<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $conflicts = DB::table('umkm_profiles')->whereNotNull('traditional_house_id')->where('profile_type', 'house')
            ->groupBy('traditional_house_id')->havingRaw('COUNT(*) > 1')->pluck('traditional_house_id');
        if ($conflicts->isNotEmpty()) {
            throw new RuntimeException('Terdapat lebih dari satu profil Rumah untuk rumah ID: '.$conflicts->implode(', ').'. Selesaikan konflik profil utama sebelum migrasi.');
        }

        $duplicates = DB::table('umkm_profiles')->whereNotNull('traditional_house_id')
            ->groupBy('traditional_house_id')->havingRaw('COUNT(*) > 1')->pluck('traditional_house_id');
        foreach ($duplicates as $houseId) {
            $profiles = DB::table('umkm_profiles')->where('traditional_house_id', $houseId)->orderBy('id')->get(['id', 'profile_type']);
            $primary = $profiles->firstWhere('profile_type', 'house');
            if (! $primary) {
                throw new RuntimeException('Rumah ID '.$houseId.' memiliki beberapa UMKM tanpa profil Rumah utama. Tentukan profil utama sebelum migrasi.');
            }
        }

        foreach ($duplicates as $houseId) {
            $primaryId = DB::table('umkm_profiles')->where('traditional_house_id', $houseId)->where('profile_type', 'house')->value('id');
            DB::table('umkm_profiles')->where('traditional_house_id', $houseId)->where('id', '!=', $primaryId)
                ->update(['traditional_house_id' => null]);
        }

        Schema::table('umkm_profiles', function (Blueprint $table): void {
            $table->unique('traditional_house_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Detached additional businesses remain intact. Rollback does not restore their previous house links.
        Schema::table('umkm_profiles', function (Blueprint $table): void {
            $table->dropForeign(['traditional_house_id']);
            $table->dropUnique(['traditional_house_id']);
            $table->foreign('traditional_house_id')->references('id')->on('traditional_houses')->nullOnDelete();
        });
    }
};
