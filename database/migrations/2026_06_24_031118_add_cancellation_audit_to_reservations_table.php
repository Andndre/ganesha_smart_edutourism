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
            $table->timestamp('cancelled_at')->nullable()->after('checked_in_by');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->after('cancelled_at');
            $table->string('cancellation_type')->nullable()->after('cancelled_by');
            $table->text('cancellation_note')->nullable()->after('cancellation_type');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancelled_at', 'cancellation_type', 'cancellation_note']);
        });
    }
};
