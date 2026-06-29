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
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->nullable()->after('qr_code');
            $table->foreignId('checked_in_by')->nullable()->after('checked_in_at')->constrained('users');
            $table->index('checked_in_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropIndex(['checked_in_at']);
            $table->dropForeign(['checked_in_by']);
            $table->dropColumn(['checked_in_at', 'checked_in_by']);
        });
    }
};
