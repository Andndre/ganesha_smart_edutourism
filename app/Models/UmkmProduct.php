<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

// ponytail: owner controller only writes the first 4 fields; legacy admin form
// still writes name/description/price/unit/images/slug — left fillable so it
// keeps working until the admin flow is also refactored.
#[Fillable(['umkm_profile_id', 'umkm_product_category_id', 'stock', 'is_active', 'name', 'slug', 'description', 'price', 'unit', 'images'])]
class UmkmProduct extends Model
{
    use HasFactory;
    use HasSlug;

    // ponytail: kept for legacy admin views that still read product-level name/description;
    // owner form no longer writes these — display_* accessors prefer the category.
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'stock' => 'integer',
            'images' => 'array',
            'price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<UmkmProfile, UmkmProduct>
     */
    public function umkmProfile(): BelongsTo
    {
        return $this->belongsTo(UmkmProfile::class);
    }

    /**
     * @return BelongsTo<UmkmProductCategory, UmkmProduct>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(UmkmProductCategory::class, 'umkm_product_category_id');
    }

    /**
     * @param  Builder<UmkmProduct>  $query
     * @return Builder<UmkmProduct>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
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
     * @param  Builder<UmkmProduct>  $query
     * @return Builder<UmkmProduct>
     */
    public function scopeInProfile(Builder $query, int $profileId)
    {
        return $query->where('umkm_profile_id', $profileId);
    }

    public function isInStock(): bool
    {
        return $this->stock === null || $this->stock > 0;
    }

    // Display proxies — product info now lives on the category.

    public function getDisplayNameAttribute(): ?string
    {
        return translateValue($this->category?->name) ?: translateValue($this->getAttribute('name'));
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        return translateValue($this->category?->description) ?: translateValue($this->getAttribute('description'));
    }

    public function getDisplayPriceAttribute(): ?string
    {
        return $this->category?->price ?? $this->getAttribute('price');
    }

    public function getDisplayUnitAttribute(): ?string
    {
        return $this->category?->unit ?: ($this->getAttribute('unit') ?: 'pcs');
    }

    public function getDisplayImageAttribute(): ?string
    {
        if ($this->category?->image_path) {
            return $this->category->image_path;
        }
        $images = $this->getAttribute('images');

        return is_array($images) && ! empty($images) ? $images[0] : null;
    }
}
