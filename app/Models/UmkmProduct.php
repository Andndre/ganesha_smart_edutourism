<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['umkm_profile_id', 'umkm_product_category_id', 'name', 'slug', 'description', 'price', 'stock', 'unit', 'images', 'is_active'])]
class UmkmProduct extends Model
{
    use HasFactory;
    use HasSlug;
    use HasTranslations;

    public array $translatable = ['name', 'description'];

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
     * Get the category of this product.
     *
     * @return BelongsTo<UmkmProductCategory, UmkmProduct>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(UmkmProductCategory::class, 'umkm_product_category_id');
    }

    /**
     * Scope a query to only include active products.
     *
     * @param  Builder<UmkmProduct>  $query
     * @return Builder<UmkmProduct>
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include products with stock.
     *
     * @param  Builder<UmkmProduct>  $query
     * @return Builder<UmkmProduct>
     */
    public function scopeInStock(Builder $query)
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
    public function scopeInProfile(Builder $query, int $profileId)
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
