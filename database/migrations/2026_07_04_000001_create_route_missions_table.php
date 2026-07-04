<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_route_point_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // matching | sequence | word_search | decision | riddle
            $table->json('title'); // translatable {en, id}
            $table->json('config'); // per-type payload; nested {en, id} leaves resolved via localizedConfig()
            $table->integer('points')->default(100);
            $table->integer('time_limit_seconds')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['tour_route_point_id', 'order']);
        });

        Schema::table('route_sessions', function (Blueprint $table) {
            $table->json('missions_completed')->nullable()->after('total_score');
        });
    }

    public function down(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            $table->dropColumn('missions_completed');
        });

        Schema::dropIfExists('route_missions');
    }
};
