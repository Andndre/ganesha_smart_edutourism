<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['name', 'slug', 'description', 'category', 'latitude', 'longitude', 'ar_marker_id', 'model_3d_path', 'historical_images', 'audio_narration_path'])]
class CulturalObject extends Model
{
  use HasFactory;

  protected $casts = [
    'historical_images' => 'array',
  ];

  /**
   * Get the stories associated with this cultural object.
   */
  public function stories(): HasMany
  {
    return $this->hasMany(CulturalStory::class)->orderBy('order');
  }

  /**
   * Get the time travel reconstructions for this cultural object.
   */
  public function timeTravels(): HasMany
  {
    return $this->hasMany(TimeTravelReconstruction::class);
  }

  /**
   * Get the map location for this cultural object.
   */
  public function mapLocation(): MorphOne
  {
    return $this->morphOne(MapLocation::class, 'locationable');
  }

  /**
   * Get learning contents associated with this cultural object.
   */
  public function learningContents(): MorphMany
  {
    return $this->morphMany(LearningContent::class, 'locationable');
  }
}