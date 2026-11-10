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
        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->foreignId('traditional_house_id')->nullable()->constrained('traditional_houses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('traditional_house_id');
        });
    }
};
