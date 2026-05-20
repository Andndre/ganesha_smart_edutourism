<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'category', 'start_datetime', 'end_datetime', 'location_name', 'latitude', 'longitude', 'is_free', 'price', 'max_participants', 'current_participants', 'registration_url'])]
class Event extends Model
{
  use HasFactory;

  protected $casts = [
    'start_datetime' => 'datetime',
    'end_datetime' => 'datetime',
    'is_free' => 'boolean',
    'price' => 'decimal:2',
  ];
}