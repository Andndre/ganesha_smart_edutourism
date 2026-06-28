<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('village_settings', function (Blueprint $table) {
            $table->id();
            $table->time('open_time')->default('08:00:00');
            $table->time('close_time')->default('18:00:00');
            $table->timestamps();
        });

        // Seed default row
        DB::table('village_settings')->insert([
            'open_time'  => '08:00:00',
            'close_time' => '18:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('village_settings');
    }
};
