<?php

namespace App\Models;

use App\Models\Concerns\HasMapLocation;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

#[Fillable(['user_id', 'owner_name', 'business_name', 'slug', 'description', 'rating', 'is_active', 'recommendation_count'])]
class UmkmProfile extends Model
{
    use HasFactory;
    use HasMapLocation;
    use HasSlug;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['business_name', 'description'];

    protected function slugSourceField(): string
    {
        return 'business_name';
    }

    protected function mapLocationNameField(): string
    {
        return 'business_name';
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'rating' => 'float',
        ];
    }

    /**
     * Get the user that owns the UMKM profile.
     *
     * @return BelongsTo<User, UmkmProfile>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the products for this UMKM profile.
     *
     * @return HasMany<UmkmProduct>
     */
    public function products(): HasMany
    {
        return $this->hasMany(UmkmProduct::class);
    }

    /**
     * Get active products for this profile.
     *
     * @return HasMany<UmkmProduct>
     */
    public function activeProducts(): HasMany
    {
        return $this->hasMany(UmkmProduct::class)->active();
    }

    /**
     * Get the feedbacks/complaints for this UMKM profile.
     *
     * @return HasMany<Feedback>
     */
    public function complaints(): HasMany
    {
        return $this->hasMany(Feedback::class)->where('feedback_type', 'umkm');
    }

    /**
     * Returns ['min' => float, 'max' => float] from active products' effective prices,
     * or null when no product has a price set. Requires activeProducts.category eager-loaded.
     *
     * @return array{min: float, max: float}|null
     */
    public function getPriceRangeAttribute(): ?array
    {
        $prices = $this->activeProducts
            ->map(fn ($p) => (float) ($p->category?->price ?? $p->getRawOriginal('price')))
            ->filter()
            ->sort()
            ->values();

        if ($prices->isEmpty()) {
            return null;
        }

        return ['min' => $prices->first(), 'max' => $prices->last()];
    }

    /**
     * Get the map location for this profile.
     *
     * @return MorphOne<MapLocation>
     */
    public function mapLocation(): MorphOne
    {
        return $this->morphOne(MapLocation::class, 'locationable');
    }

    /**
     * Scope a query to only include active profiles.
     *
     * @param  Builder<UmkmProfile>  $query
     * @return Builder<UmkmProfile>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include profiles with coordinates.
     *
     * @param  Builder<UmkmProfile>  $query
     * @return Builder<UmkmProfile>
     */
    public function scopeWithCoordinates(Builder $query): Builder
    {
        return $query->whereHas('mapLocation');
    }
}
