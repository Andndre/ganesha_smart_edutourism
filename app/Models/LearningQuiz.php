<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['learning_content_id', 'question', 'options', 'explanation', 'order'])]
class LearningQuiz extends Model
{
  use HasFactory;

  protected $casts = [
    'options' => 'array',
  ];

  /**
   * Get the content that owns this quiz.
   */
  public function content(): BelongsTo
  {
    return $this->belongsTo(LearningContent::class);
  }
}