<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['name', 'category', 'locationable_type', 'locationable_id', 'latitude', 'longitude', 'is_accessible', 'accessibility_notes'])]
class MapLocation extends Model
{
  use HasFactory;

  protected $casts = [
    'is_accessible' => 'boolean',
  ];

  /**
   * Get the owning locationable model.
   */
  public function locationable(): MorphTo
  {
    return $this->morphTo();
  }
}