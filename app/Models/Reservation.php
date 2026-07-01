<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'guest_name', 'guest_email', 'tour_package_id', 'reservation_type', 'scheduled_date', 'party_size', 'total_amount', 'status', 'payment_status', 'payment_method', 'payment_reference', 'qr_code', 'checked_in_at', 'checked_in_by', 'cancelled_at', 'cancelled_by', 'cancellation_type', 'cancellation_note'])]
class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'total_amount' => 'decimal:2',
            'party_size' => 'integer',
        ];
    }

    /**
     * Get the user that made the reservation.
     *
     * @return BelongsTo<User, Reservation>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tour package for this reservation.
     *
     * @return BelongsTo<TourPackage, Reservation>
     */
    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    /**
     * Get the feedbacks for this reservation.
     *
     * @return HasMany<Feedback>
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

}
