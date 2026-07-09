<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Widen enum first so old + new values coexist during data migration.
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->enum('category', ['temple', 'house', 'craft', 'tradition', 'parahyangan', 'pawongan', 'palemahan'])->change();
        });

        // ponytail: default mapping is a rough guess (temple -> parahyangan, everything
        // else -> pawongan); admins re-tag individual objects to palemahan manually.
        DB::table('cultural_objects')->where('category', 'temple')->update(['category' => 'parahyangan']);
        DB::table('cultural_objects')->whereIn('category', ['house', 'craft', 'tradition'])->update(['category' => 'pawongan']);

        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->enum('category', ['parahyangan', 'pawongan', 'palemahan'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->enum('category', ['temple', 'house', 'craft', 'tradition', 'parahyangan', 'pawongan', 'palemahan'])->change();
        });

        DB::table('cultural_objects')->where('category', 'parahyangan')->update(['category' => 'temple']);
        DB::table('cultural_objects')->whereIn('category', ['pawongan', 'palemahan'])->update(['category' => 'house']);

        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->enum('category', ['temple', 'house', 'craft', 'tradition'])->change();
        });
    }
};
