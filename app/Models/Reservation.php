<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'guest_name', 'guest_email', 'guest_phone', 'tour_package_id', 'reservation_type', 'scheduled_date', 'scheduled_time', 'party_size', 'total_amount', 'status', 'payment_status', 'payment_method', 'payment_reference', 'qr_code'])]
class Reservation extends Model
{
  use HasFactory;

  protected $casts = [
    'scheduled_date' => 'date',
    'scheduled_time' => 'datetime:H:i',
    'total_amount' => 'decimal:2',
  ];

  /**
   * Get the user that made the reservation.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the tour package for this reservation.
   */
  public function tourPackage(): BelongsTo
  {
    return $this->belongsTo(TourPackage::class);
  }
}