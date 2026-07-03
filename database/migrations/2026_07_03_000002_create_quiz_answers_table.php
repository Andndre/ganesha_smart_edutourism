<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cultural_object_quiz_id')->constrained()->cascadeOnDelete();
            $table->char('selected_option', 1);
            $table->boolean('is_correct');
            $table->timestamps();

            $table->unique(['route_session_id', 'cultural_object_quiz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
