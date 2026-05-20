<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['learning_module_id', 'content_type', 'title', 'content', 'media_path', 'duration_seconds', 'order'])]
class LearningContent extends Model
{
  use HasFactory;

  /**
   * Get the module that owns this content.
   */
  public function module(): BelongsTo
  {
    return $this->belongsTo(LearningModule::class);
  }

  /**
   * Get the quizzes for this content.
   */
  public function quizzes(): HasMany
  {
    return $this->hasMany(LearningQuiz::class)->orderBy('order');
  }
}