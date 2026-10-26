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
        Schema::table('route_sessions', function (Blueprint $table) {
            $table->dropColumn('selected_avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('route_sessions', function (Blueprint $table) {
            $table->string('selected_avatar')->nullable()->after('badge_awarded');
        });
    }
};
