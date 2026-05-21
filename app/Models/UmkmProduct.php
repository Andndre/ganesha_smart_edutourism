<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * UmkmProduct model for umkm business products.
 *
 * @property int $id
 * @property int $umkm_profile_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property float $price
 * @property int|null $stock
 * @property string|null $unit
 * @property array|null $images
 * @property string|null $ar_model_path
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['umkm_profile_id', 'name', 'slug', 'description', 'price', 'stock', 'unit', 'images', 'ar_model_path', 'is_active'])]
class UmkmProduct extends Model
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
            'images' => 'array',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /**
     * Get the profile that owns the product.
     *
     * @return BelongsTo<UmkmProfile, UmkmProduct>
     */
    public function umkmProfile(): BelongsTo
    {
        return $this->belongsTo(UmkmProfile::class);
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  Builder<UmkmProduct>  $query
     * @return Builder<UmkmProduct>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include products with stock.
     *
     * @param  Builder<UmkmProduct>  $query
     * @return Builder<UmkmProduct>
     */
    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('stock')->orWhere('stock', '>', 0);
        });
    }

    /**
     * Scope a query to filter by profile.
     *
     * @param  Builder<UmkmProduct>  $query
     * @return Builder<UmkmProduct>
     */
    public function scopeInProfile($query, int $profileId)
    {
        return $query->where('umkm_profile_id', $profileId);
    }

    /**
     * Check if the product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }
}
