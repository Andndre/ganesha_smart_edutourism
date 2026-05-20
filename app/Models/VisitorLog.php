<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['session_id', 'user_id', 'event_type', 'event_data', 'latitude', 'longitude', 'device_type', 'browser', 'nationality', 'logged_at'])]
class VisitorLog extends Model
{
  use HasFactory;

  protected $casts = [
    'event_data' => 'array',
    'logged_at' => 'datetime',
  ];

  /**
   * Get the user associated with this log.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}