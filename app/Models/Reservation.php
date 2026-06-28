<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'guest_name', 'guest_email', 'guest_phone', 'tour_package_id', 'reservation_type', 'scheduled_date', 'party_size', 'total_amount', 'status', 'payment_status', 'payment_method', 'payment_reference', 'qr_code', 'checked_in_at', 'checked_in_by', 'cancelled_at', 'cancelled_by', 'cancellation_type', 'cancellation_note'])]
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

    /**
     * Scope a query to filter by status.
     *
     * @param  Builder<Reservation>  $query
     * @return Builder<Reservation>
     */
    public function scopeStatus(Builder $query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by payment status.
     *
     * @param  Builder<Reservation>  $query
     * @return Builder<Reservation>
     */
    public function scopePaymentStatus(Builder $query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Scope a query to include confirmed reservations.
     *
     * @param  Builder<Reservation>  $query
     * @return Builder<Reservation>
     */
    public function scopeConfirmed(Builder $query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to include upcoming reservations.
     *
     * @param  Builder<Reservation>  $query
     * @return Builder<Reservation>
     */
    public function scopeUpcoming(Builder $query)
    {
        return $query->whereDate('scheduled_date', '>=', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('scheduled_date');
    }

    /**
     * Scope a query to filter by reservation type.
     *
     * @param  Builder<Reservation>  $query
     * @return Builder<Reservation>
     */
    public function scopeOfType(Builder $query, string $type)
    {
        return $query->where('reservation_type', $type);
    }
}
