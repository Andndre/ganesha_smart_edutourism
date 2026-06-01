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
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->string('ar_marker_patt_path')->nullable()->after('ar_marker_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->dropColumn('ar_marker_patt_path');
        });
    }
};
