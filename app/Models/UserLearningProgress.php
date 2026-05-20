<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'learning_module_id', 'status', 'progress_percentage', 'last_content_id', 'completed_at'])]
class UserLearningProgress extends Model
{
  use HasFactory;

  protected $casts = [
    'completed_at' => 'datetime',
  ];

  /**
   * Get the user that owns this progress.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the learning module for this progress.
   */
  public function module(): BelongsTo
  {
    return $this->belongsTo(LearningModule::class, 'learning_module_id');
  }

  /**
   * Get the last content viewed.
   */
  public function lastContent(): BelongsTo
  {
    return $this->belongsTo(LearningContent::class, 'last_content_id');
  }
}