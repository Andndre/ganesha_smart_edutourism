<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'difficulty', 'estimated_duration_minutes', 'distance_meters', 'is_smart_route', 'is_active'])]
class TourRoute extends Model
{
  use HasFactory;

  protected $casts = [
    'is_smart_route' => 'boolean',
    'is_active' => 'boolean',
  ];

  /**
   * Get the route points for this tour route.
   */
  public function routePoints(): HasMany
  {
    return $this->hasMany(TourRoutePoint::class)->orderBy('order');
  }
}