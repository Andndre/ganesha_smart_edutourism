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
        Schema::create('route_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_token')->nullable()->index();
            $table->foreignId('tour_route_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_point_id')->nullable()->constrained('tour_route_points')->nullOnDelete();
            $table->integer('points_completed')->default(0);
            $table->integer('total_score')->default(0);
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_sessions');
    }
};
