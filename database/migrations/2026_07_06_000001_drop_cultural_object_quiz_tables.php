<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Point-1 quizzes are now RouteMission records (type 'quiz'), authored from the
     * unified "Kelola Misi" drawer instead of a separate cultural-object quiz form.
     */
    public function up(): void
    {
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('cultural_object_quizzes');
    }

    public function down(): void
    {
        Schema::create('cultural_object_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cultural_object_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->string('option_a');
            $table->string('option_b');
            $table->string('option_c');
            $table->string('option_d');
            $table->char('correct_option', 1);
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

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
};
