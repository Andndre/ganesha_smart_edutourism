<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cultural_object_id', 'year_represented', 'title', 'description', 'model_3d_path'])]
class TimeTravelReconstruction extends Model
{
  use HasFactory;

  /**
   * Get the cultural object that owns the reconstruction.
   */
  public function culturalObject(): BelongsTo
  {
    return $this->belongsTo(CulturalObject::class);
  }
}