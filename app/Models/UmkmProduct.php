<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['umkm_profile_id', 'name', 'slug', 'description', 'price', 'stock', 'unit', 'images', 'ar_model_path', 'is_active'])]
class UmkmProduct extends Model
{
  use HasFactory;

  protected $casts = [
    'images' => 'array',
    'is_active' => 'boolean',
    'price' => 'decimal:2',
  ];

  /**
   * Get the profile that owns the product.
   */
  public function umkmProfile(): BelongsTo
  {
    return $this->belongsTo(UmkmProfile::class);
  }
}