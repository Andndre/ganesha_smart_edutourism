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
        Schema::create('cultural_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultural_object_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->enum('story_type', ['history', 'philosophy', 'value']);
            $table->integer('order')->default(0);
            $table->timestamps();

            // Composite index for ordering stories within an object
            $table->index(['cultural_object_id', 'order']);
        });

        Schema::create('time_travel_reconstructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultural_object_id')->constrained()->cascadeOnDelete();
            $table->integer('year_represented');
            $table->string('title');
            $table->text('description');
            $table->string('model_3d_path');
            $table->timestamps();

            // Index for chronological ordering
            $table->index('year_represented');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_travel_reconstructions');
        Schema::dropIfExists('cultural_stories');
    }
};
