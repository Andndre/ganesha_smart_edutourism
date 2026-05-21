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
        Schema::create('cultural_objects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->enum('category', ['temple', 'house', 'craft', 'tradition']);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('ar_marker_id')->unique();
            $table->string('model_3d_path')->nullable();
            $table->json('historical_images')->nullable();
            $table->string('audio_narration_path')->nullable();
            $table->timestamps();

            // Indexes for filtering by category and sorting by date
            $table->index('category');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cultural_objects');
    }
};
