<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_scans', function (Blueprint $table) {
            $table->id();
            $table->char('code_hash', 64)->unique();
            $table->text('raw_code');
            $table->string('visitor_name')->nullable();
            $table->unsignedInteger('party_size')->default(1);
            $table->enum('origin', ['domestic', 'foreign'])->default('domestic');
            $table->dateTime('scanned_at');
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('duplicate_attempts')->default(0);
            $table->dateTime('last_attempt_at')->nullable();
            $table->timestamps();

            $table->index('scanned_at');
            $table->index('origin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_scans');
    }
};
