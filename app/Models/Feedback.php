<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'reservation_id', 'feedback_type', 'rating', 'comment', 'photos', 'is_public', 'admin_response'])]
class Feedback extends Model
{
  use HasFactory;

  protected $casts = [
    'photos' => 'array',
    'is_public' => 'boolean',
  ];

  /**
   * Get the user that gave the feedback.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the reservation associated with this feedback.
   */
  public function reservation(): BelongsTo
  {
    return $this->belongsTo(Reservation::class);
  }
}