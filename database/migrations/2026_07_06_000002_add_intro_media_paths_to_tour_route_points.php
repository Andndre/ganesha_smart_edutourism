<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_route_points', function (Blueprint $table) {
            $table->json('intro_video_paths')->nullable()->after('storytelling_content');
            $table->json('intro_audio_paths')->nullable()->after('intro_video_paths');
        });
    }

    public function down(): void
    {
        Schema::table('tour_route_points', function (Blueprint $table) {
            $table->dropColumn(['intro_video_paths', 'intro_audio_paths']);
        });
    }
};
