<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * Event model for events and activities.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $category
 * @property Carbon $start_datetime
 * @property Carbon $end_datetime
 * @property string|null $location_name
 * @property bool $is_free
 * @property float|null $price
 * @property int|null $max_participants
 * @property int $current_participants
 * @property string|null $registration_url
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'description', 'category', 'start_datetime', 'end_datetime', 'location_name', 'is_free', 'price', 'max_participants', 'current_participants', 'registration_url'])]
class Event extends Model
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
            'start_datetime' => 'datetime',
            'end_datetime' => 'datetime',
            'is_free' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::deleted(function (Event $event) {
            $event->mapLocation()->delete();
        });
    }

    /**
     * Get the map location for this event.
     *
     * @return MorphOne<MapLocation>
     */
    public function mapLocation(): MorphOne
    {
        return $this->morphOne(MapLocation::class, 'locationable');
    }

    /**
     * Get the reservations for this event.
     *
     * @return HasMany<Reservation>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'reservation_type', 'event');
    }

    /**
     * Scope a query to only include upcoming events.
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeUpcoming(Builder $query)
    {
        return $query->where('start_datetime', '>', now())->orderBy('start_datetime');
    }

    /**
     * Scope a query to only include ongoing events.
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeOngoing(Builder $query)
    {
        return $query->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now());
    }

    /**
     * Scope a query to only include free events.
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeFree(Builder $query)
    {
        return $query->where('is_free', true);
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeCategory(Builder $query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to include events with available spots.
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeAvailable(Builder $query)
    {
        return $query->where(function ($q) {
            $q->whereNull('max_participants')
                ->orWhereRaw('current_participants < max_participants');
        });
    }

    /**
     * Check if the event is currently happening.
     */
    public function isOngoing(): bool
    {
        $now = now();

        return $this->start_datetime <= $now && $this->end_datetime >= $now;
    }

    /**
     * Check if the event has available spots.
     */
    public function hasAvailability(): bool
    {
        if ($this->max_participants === null) {
            return true;
        }

        return $this->current_participants < $this->max_participants;
    }

    /**
     * Get the category display label.
     */
    public function getCategoryLabel(): string
    {
        $map = [
            'ceremony' => 'Upacara Adat',
            'cultural' => 'Festival',
            'workshop' => 'Workshop',
            'culinary' => 'Kuliner',
        ];

        return $map[$this->category] ?? $this->category;
    }
}
