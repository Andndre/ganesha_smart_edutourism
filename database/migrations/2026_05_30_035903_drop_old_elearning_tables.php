<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('user_learning_progress');
        Schema::dropIfExists('learning_quizzes');
        Schema::dropIfExists('learning_contents');
        Schema::dropIfExists('learning_modules');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One way trip for cleanup
    }
};
