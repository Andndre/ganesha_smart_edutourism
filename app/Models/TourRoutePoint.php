<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['tour_route_id', 'locationable_type', 'locationable_id', 'order', 'estimated_visit_minutes', 'storytelling_content'])]
class TourRoutePoint extends Model
{
  use HasFactory;

  /**
   * Get the tour route that owns this point.
   */
  public function tourRoute(): BelongsTo
  {
    return $this->belongsTo(TourRoute::class);
  }

  /**
   * Get the owning locationable model.
   */
  public function locationable(): MorphTo
  {
    return $this->morphTo();
  }
}