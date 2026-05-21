<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * TourPackage model for tourism packages.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array|null $inclusions
 * @property array|null $exclusions
 * @property float $price
 * @property float|null $duration_hours
 * @property int|null $max_capacity
 * @property int|null $min_capacity
 * @property array|null $images
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'description', 'inclusions', 'exclusions', 'price', 'duration_hours', 'max_capacity', 'min_capacity', 'images', 'is_active'])]
class TourPackage extends Model
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
            'inclusions' => 'array',
            'exclusions' => 'array',
            'images' => 'array',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
            'duration_hours' => 'decimal:1',
        ];
    }

    /**
     * Get the reservations for this package.
     *
     * @return HasMany<Reservation>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Scope a query to only include active packages.
     *
     * @param  Builder<TourPackage>  $query
     * @return Builder<TourPackage>
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by price range.
     *
     * @param  Builder<TourPackage>  $query
     * @return Builder.Builder<TourPackage>
     */
    public function scopePriceRange(Builder $query, float $minPrice, float $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }
}
