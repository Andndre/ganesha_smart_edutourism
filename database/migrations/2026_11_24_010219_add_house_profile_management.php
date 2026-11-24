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
        $duplicates = DB::table('umkm_profiles')->whereNotNull('user_id')->groupBy('user_id')->havingRaw('COUNT(*) > 1')->pluck('user_id');
        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Akun memiliki lebih dari satu profil UMKM: '.$duplicates->implode(', '));
        }
        Schema::table('umkm_profiles', function (Blueprint $table): void {
            $table->string('profile_type')->default('business')->index();
            $table->unique('user_id');
        });
        Schema::table('traditional_houses', function (Blueprint $table): void {
            $table->foreignId('owner_user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traditional_houses', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_user_id');
        });
        Schema::table('umkm_profiles', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
            $table->dropColumn('profile_type');
        });
    }
};
