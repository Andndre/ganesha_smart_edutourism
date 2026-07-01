<?php

namespace App\Models;

use App\Models\Concerns\HasMapLocation;
use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'category', 'start_datetime', 'end_datetime', 'location_name', 'is_free', 'price', 'max_participants', 'current_participants', 'registration_url'])]
class Event extends Model
{
    use HasFactory;
    use HasMapLocation;
    use HasSlug;
    use HasTranslations;

    public array $translatable = ['name', 'description', 'location_name'];

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
