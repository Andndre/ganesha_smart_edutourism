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
        if (! Schema::hasColumn('umkm_profiles', 'image_path')) {
            Schema::table('umkm_profiles', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('description');
            });
        }

        // Set default cover image for all existing UMKM profiles
        DB::table('umkm_profiles')->update([
            'image_path' => 'images/packages/kuliner-paon.webp',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('umkm_profiles', 'image_path')) {
            Schema::table('umkm_profiles', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
};

