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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('category', ['cultural', 'culinary', 'workshop', 'ceremony']);
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->string('location_name');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_free')->default(false);
            $table->decimal('price', 12, 2)->nullable();
            $table->integer('max_participants')->nullable();
            $table->integer('current_participants')->default(0);
            $table->string('registration_url')->nullable();
            $table->timestamps();

            // Indexes for filtering upcoming events
            $table->index('start_datetime');
            $table->index('category');
        });

        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('duration_hours', 4, 1);
            $table->integer('max_capacity');
            $table->integer('min_capacity')->default(1);
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index for active packages
            $table->index('is_active');
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email');
            $table->string('guest_phone');
            $table->foreignId('tour_package_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('reservation_type', ['package', 'custom_tour', 'event']);
            $table->date('scheduled_date');
            $table->time('scheduled_time');
            $table->integer('party_size');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('qr_code')->unique();
            $table->timestamps();

            // Indexes for filtering reservations
            $table->index('status');
            $table->index('payment_status');
            $table->index('scheduled_date');
            $table->index(['scheduled_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('tour_packages');
        Schema::dropIfExists('events');
    }
};
