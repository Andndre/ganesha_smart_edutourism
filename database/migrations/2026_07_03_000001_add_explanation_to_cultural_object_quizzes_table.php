<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cultural_object_quizzes', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('correct_option');
        });
    }

    public function down(): void
    {
        Schema::table('cultural_object_quizzes', function (Blueprint $table) {
            $table->dropColumn('explanation');
        });
    }
};
