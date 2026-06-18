<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['user_id', 'owner_name', 'business_name', 'slug', 'description', 'category', 'rating', 'is_active', 'recommendation_count', 'accepts_in_app_payment'])]
class UmkmProfile extends Model
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
            'is_active' => 'boolean',
            'rating' => 'float',
            'accepts_in_app_payment' => 'boolean',
        ];
    }

    /**
     * The booted method of the model.
     */
    protected static function booted(): void
    {
        static::deleted(function (UmkmProfile $umkmProfile) {
            $umkmProfile->mapLocation()->delete();
        });
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
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  Builder<UmkmProfile>  $query
     * @return Builder<UmkmProfile>
     */
    public function scopeCategory(Builder $query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to include profiles with coordinates.
     *
     * @param  Builder<UmkmProfile>  $query
     * @return Builder<UmkmProfile>
     */
    public function scopeWithCoordinates(Builder $query)
    {
        return $query->whereHas('mapLocation');
    }

    /**
     * Accessor for ar_marker_id.
     */
    public function getArMarkerIdAttribute(): ?string
    {
        return $this->mapLocation?->arMarker?->ar_marker_id;
    }
}
