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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('feedback_type', ['general', 'cultural', 'service', 'facility', 'umkm']);
            $table->integer('rating');
            $table->text('comment')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('is_public')->default(true);
            $table->text('admin_response')->nullable();
            $table->timestamps();

            // Indexes for filtering feedback
            $table->index('feedback_type');
            $table->index('is_public');
            $table->index('rating');
        });

        Schema::create('capacity_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone_identifier')->unique();
            $table->integer('max_capacity');
            $table->integer('warning_threshold')->default(70);
            $table->integer('critical_threshold')->default(90);
            $table->integer('current_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index for active zones
            $table->index('is_active');
        });

        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('event_type', ['page_view', 'feature_use', 'location_visit', 'purchase']);
            $table->json('event_data')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('nationality')->nullable();
            $table->datetime('logged_at');
            $table->timestamps();

            // Indexes for querying visitor logs
            $table->index('session_id');
            $table->index('logged_at');
            $table->index('event_type');
            $table->index(['session_id', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('capacity_zones');
        Schema::dropIfExists('feedbacks');
    }
};
