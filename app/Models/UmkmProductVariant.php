<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['umkm_product_id', 'label', 'price', 'stock', 'is_active', 'sort_order'])]
class UmkmProductVariant extends Model
{
    use HasFactory;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['label'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'stock' => 'integer', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(UmkmProduct::class, 'umkm_product_id');
    }
}
