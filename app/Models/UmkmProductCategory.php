<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'image_path', 'model_3d_path', 'model_3d_usdz_path'])]
class UmkmProductCategory extends Model
{
    use HasFactory;

    /**
     * Get the products in this category.
     *
     * @return HasMany<UmkmProduct>
     */
    public function products(): HasMany
    {
        return $this->hasMany(UmkmProduct::class, 'umkm_product_category_id');
    }
}
