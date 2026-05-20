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
    Schema::create('learning_modules', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('slug')->unique();
      $table->enum('category', ['history', 'craft', 'culinary', 'tradition', 'environment']);
      $table->text('description')->nullable();
      $table->string('thumbnail_path')->nullable();
      $table->enum('difficulty', ['beginner', 'intermediate', 'advanced']);
      $table->integer('estimated_duration_minutes');
      $table->boolean('is_active')->default(true);
      $table->integer('order')->default(0);
      $table->timestamps();
    });

    Schema::create('learning_contents', function (Blueprint $table) {
      $table->id();
      $table->foreignId('learning_module_id')->constrained()->cascadeOnDelete();
      $table->enum('content_type', ['text', 'image', 'video', 'audio', 'quiz']);
      $table->string('title');
      $table->text('content');
      $table->string('media_path')->nullable();
      $table->integer('duration_seconds')->nullable();
      $table->integer('order')->default(0);
      $table->timestamps();
    });

    Schema::create('learning_quizzes', function (Blueprint $table) {
      $table->id();
      $table->foreignId('learning_content_id')->constrained()->cascadeOnDelete();
      $table->text('question');
      $table->json('options');
      $table->text('explanation')->nullable();
      $table->integer('order')->default(0);
      $table->timestamps();
    });

    Schema::create('user_learning_progress', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->foreignId('learning_module_id')->constrained()->cascadeOnDelete();
      $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
      $table->integer('progress_percentage')->default(0);
      $table->foreignId('last_content_id')->nullable()->constrained('learning_contents')->nullOnDelete();
      $table->datetime('completed_at')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_learning_progress');
    Schema::dropIfExists('learning_quizzes');
    Schema::dropIfExists('learning_contents');
    Schema::dropIfExists('learning_modules');
  }
};