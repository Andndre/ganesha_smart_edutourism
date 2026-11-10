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
        Schema::create('traditional_houses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number')->unique();
            $table->string('head_name');
            $table->unsignedSmallInteger('male_count')->default(0);
            $table->unsignedSmallInteger('female_count')->default(0);
            $table->json('description')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->json('gallery')->nullable();
            $table->string('ar_marker_id')->nullable()->unique();
            $table->boolean('is_open_to_visitors')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->index(['is_published', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traditional_houses');
    }
};
