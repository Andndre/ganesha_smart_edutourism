<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('traditional_houses')->update([
            'is_open_to_visitors' => true,
            'is_published' => true,
        ]);

        if (Cache::supportsTags()) {
            Cache::tags(['cultural', 'explore'])->flush();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Previous publication and visitor settings cannot be reconstructed safely.
    }
};
