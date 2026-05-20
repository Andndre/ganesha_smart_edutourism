<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'category', 'description', 'thumbnail_path', 'difficulty', 'estimated_duration_minutes', 'is_active', 'order'])]
class LearningModule extends Model
{
  use HasFactory;

  protected $casts = [
    'is_active' => 'boolean',
  ];

  /**
   * Get the contents for this module.
   */
  public function contents(): HasMany
  {
    return $this->hasMany(LearningContent::class)->orderBy('order');
  }
}