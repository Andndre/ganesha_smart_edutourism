<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'inclusions', 'exclusions', 'price', 'duration_hours', 'max_capacity', 'min_capacity', 'images', 'is_active'])]
class TourPackage extends Model
{
  use HasFactory;

  protected $casts = [
    'inclusions' => 'array',
    'exclusions' => 'array',
    'images' => 'array',
    'is_active' => 'boolean',
    'price' => 'decimal:2',
    'duration_hours' => 'decimal:1',
  ];
}