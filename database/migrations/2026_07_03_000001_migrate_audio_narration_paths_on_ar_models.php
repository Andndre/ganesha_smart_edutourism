<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ar_models', function (Blueprint $table) {
            $table->json('audio_narration_paths')->nullable()->after('audio_narration_path');
        });

        // Migrate existing single path → { "en": "..." }
        DB::table('ar_models')->whereNotNull('audio_narration_path')->orderBy('id')->each(function ($row) {
            DB::table('ar_models')->where('id', $row->id)->update([
                'audio_narration_paths' => json_encode(['en' => $row->audio_narration_path]),
            ]);
        });

        Schema::table('ar_models', function (Blueprint $table) {
            $table->dropColumn('audio_narration_path');
        });
    }

    public function down(): void
    {
        Schema::table('ar_models', function (Blueprint $table) {
            $table->string('audio_narration_path')->nullable()->after('audio_narration_paths');
        });

        DB::table('ar_models')->whereNotNull('audio_narration_paths')->orderBy('id')->each(function ($row) {
            $paths = json_decode($row->audio_narration_paths, true) ?? [];
            $path = $paths['en'] ?? $paths['id'] ?? null;
            if ($path) {
                DB::table('ar_models')->where('id', $row->id)->update(['audio_narration_path' => $path]);
            }
        });

        Schema::table('ar_models', function (Blueprint $table) {
            $table->dropColumn('audio_narration_paths');
        });
    }
};
