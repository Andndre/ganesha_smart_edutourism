<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Feedback model for user reviews and ratings.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $reservation_id
 * @property string|null $feedback_type
 * @property int|null $rating
 * @property string|null $comment
 * @property array|null $photos
 * @property bool $is_public
 * @property string|null $admin_response
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'reservation_id', 'feedback_type', 'rating', 'comment', 'photos', 'is_public', 'admin_response'])]
class Feedback extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'feedbacks';

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get the user that gave the feedback.
     *
     * @return BelongsTo<User, Feedback>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reservation associated with this feedback.
     *
     * @return BelongsTo<Reservation, Feedback>
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Scope a query to only include public feedback.
     *
     * @param  Builder<Feedback>  $query
     * @return Builder<Feedback>
     */
    public function scopePublic(Builder $query)
    {
        return $query->where('is_public', true)->orderBy('created_at', 'desc');
    }

    /**
     * Scope a query to filter by feedback type.
     *
     * @param  Builder<Feedback>  $query
     * @return Builder<Feedback>
     */
    public function scopeOfType(Builder $query, string $type)
    {
        return $query->where('feedback_type', $type);
    }

    /**
     * Scope a query to filter by minimum rating.
     *
     * @param  Builder<Feedback>  $query
     * @return Builder<Feedback>
     */
    public function scopeMinRating(Builder $query, int $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope a query to include only reviewed feedback (with admin response).
     *
     * @param  Builder<Feedback>  $query
     * @return Builder<Feedback>
     */
    public function scopeReviewed(Builder $query)
    {
        return $query->whereNotNull('admin_response');
    }
}
