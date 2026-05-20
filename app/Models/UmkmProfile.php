<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['user_id', 'owner_name', 'business_name', 'slug', 'description', 'category', 'latitude', 'longitude', 'ar_marker_id', 'rating', 'is_active'])]
class UmkmProfile extends Model
{
  use HasFactory;

  protected $casts = [
    'is_active' => 'boolean',
  ];

  /**
   * Get the user that owns the UMKM profile.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the products for this UMKM profile.
   */
  public function products(): HasMany
  {
    return $this->hasMany(UmkmProduct::class);
  }

  /**
   * Get the map location for this profile.
   */
  public function mapLocation(): MorphOne
  {
    return $this->morphOne(MapLocation::class, 'locationable');
  }
}