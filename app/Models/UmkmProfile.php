<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

/**
 * UmkmProfile model for umkm business profiles.
 *
 * @property int $id
 * @property int $user_id
 * @property string $owner_name
 * @property string $business_name
 * @property string $slug
 * @property string|null $description
 * @property string|null $category
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $ar_marker_id
 * @property float|null $rating
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'owner_name', 'business_name', 'slug', 'description', 'category', 'latitude', 'longitude', 'ar_marker_id', 'rating', 'is_active'])]
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
            'latitude' => 'float',
            'longitude' => 'float',
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
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }
}
