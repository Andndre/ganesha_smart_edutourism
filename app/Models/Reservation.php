<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Reservation model for tour and event reservations.
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $guest_name
 * @property string|null $guest_email
 * @property string|null $guest_phone
 * @property int|null $tour_package_id
 * @property string|null $reservation_type
 * @property Request|null $scheduled_date
 * @property string|null $scheduled_time
 * @property int $party_size
 * @property float $total_amount
 * @property string|null $status
 * @property string|null $payment_status
 * @property string|null $payment_method
 * @property string|null $payment_reference
 * @property string|null $qr_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'guest_name', 'guest_email', 'guest_phone', 'tour_package_id', 'reservation_type', 'scheduled_date', 'scheduled_time', 'party_size', 'total_amount', 'status', 'payment_status', 'payment_method', 'payment_reference', 'qr_code'])]
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
            'scheduled_time' => 'datetime:H:i',
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
