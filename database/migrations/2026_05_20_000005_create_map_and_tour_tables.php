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
    Schema::create('map_locations', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->enum('category', ['cultural', 'umkm', 'facility', 'emergency', 'accessibility']);
      $table->morphs('locationable');
      $table->decimal('latitude', 10, 8);
      $table->decimal('longitude', 11, 8);
      $table->boolean('is_accessible')->default(true);
      $table->text('accessibility_notes')->nullable();
      $table->timestamps();
    });

    Schema::create('tour_routes', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->text('description')->nullable();
      $table->enum('difficulty', ['easy', 'moderate', 'challenging']);
      $table->integer('estimated_duration_minutes');
      $table->integer('distance_meters');
      $table->boolean('is_smart_route')->default(false);
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });

    Schema::create('tour_route_points', function (Blueprint $table) {
      $table->id();
      $table->foreignId('tour_route_id')->constrained()->cascadeOnDelete();
      $table->morphs('locationable');
      $table->integer('order');
      $table->integer('estimated_visit_minutes')->default(15);
      $table->text('storytelling_content')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tour_route_points');
    Schema::dropIfExists('tour_routes');
    Schema::dropIfExists('map_locations');
  }
};