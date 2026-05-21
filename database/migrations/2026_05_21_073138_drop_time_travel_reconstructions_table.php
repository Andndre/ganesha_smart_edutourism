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
        Schema::dropIfExists('time_travel_reconstructions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('time_travel_reconstructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultural_object_id')->constrained()->cascadeOnDelete();
            $table->integer('year_represented')->index();
            $table->string('title');
            $table->text('description');
            $table->string('model_3d_path');
            $table->timestamps();
        });
    }
};
