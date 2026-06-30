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
}
