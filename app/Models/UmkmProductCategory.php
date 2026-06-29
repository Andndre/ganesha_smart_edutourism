<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'price', 'unit', 'image_path', 'model_3d_path', 'model_3d_usdz_path'])]
class UmkmProductCategory extends Model
{
    use HasFactory;
    use HasSlug;
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get the products in this category.
     *
     * @return HasMany<UmkmProduct>
     */
    public function products(): HasMany
    {
        return $this->hasMany(UmkmProduct::class, 'umkm_product_category_id');
    }

    /**
     * True if every product in this category belongs to the given profile
     * (or the category has no products yet).
     */
    public function editableByOwner(UmkmProfile $profile): bool
    {
        return ! $this->products()
            ->where('umkm_profile_id', '!=', $profile->id)
            ->exists();
    }
}
