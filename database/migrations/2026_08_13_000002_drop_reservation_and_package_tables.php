<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Favorit yang menunjuk paket tur tidak punya target lagi.
        DB::table('user_favorites')
            ->where('favoritable_type', 'App\\Models\\TourPackage')
            ->delete();

        if (Schema::hasColumn('feedbacks', 'reservation_id')) {
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->dropConstrainedForeignId('reservation_id');
            });
        }

        Schema::dropIfExists('reservations');
        Schema::dropIfExists('tour_packages');
    }

    public function down(): void
    {
        // Mengembalikan struktur, bukan isinya — data reservasi lama
        // seluruhnya data uji dan tidak dimigrasikan.
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
            $table->index('is_active');
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->foreignId('tour_package_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('reservation_type', ['package', 'custom_tour', 'event', 'ticket']);
            $table->date('scheduled_date');
            $table->integer('party_size');
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('qr_code')->unique();
            $table->timestamps();
            $table->index('status');
            $table->index('scheduled_date');
        });

        Schema::table('feedbacks', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }
};
