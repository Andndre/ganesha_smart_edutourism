<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'description', 'inclusions', 'exclusions', 'price', 'duration_hours', 'max_capacity', 'min_capacity', 'images', 'is_active'])]
class TourPackage extends Model
{
    use HasFactory;
    use HasSlug;
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    /**
     * Get raw inclusions for a specific locale (admin forms).
     */
    public function getInclusionsForLocale(string $locale): array
    {
        $raw = $this->getRawOriginal('inclusions');
        $decoded = $raw ? json_decode($raw, true) : [];

        if (! \is_array($decoded) || array_is_list($decoded)) {
            return $decoded ?? [];
        }

        return $decoded[$locale] ?? [];
    }

    /**
     * Get raw exclusions for a specific locale (admin forms).
     */
    public function getExclusionsForLocale(string $locale): array
    {
        $raw = $this->getRawOriginal('exclusions');
        $decoded = $raw ? json_decode($raw, true) : [];

        if (! \is_array($decoded) || array_is_list($decoded)) {
            return $decoded ?? [];
        }

        return $decoded[$locale] ?? [];
    }

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
            'duration_hours' => 'decimal:1',
        ];
    }

    /**
     * Override attributesToArray to handle Spatie translatable attributes.
     * Spatie's getAttributeValue() override is not called by Laravel's default
     * attributesToArray(), so translatable fields (name, description) would
     * be serialized as raw JSON strings in toArray() output.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getTranslatableAttributes() as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->getAttributeValue($key);
            }
        }

        return $attributes;
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
}
