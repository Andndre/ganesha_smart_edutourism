<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'type', 'description', 'inclusions', 'exclusions', 'itinerary', 'price', 'duration_hours', 'max_capacity', 'min_capacity', 'images', 'is_active'])]
class TourPackage extends Model
{
    use HasFactory;
    use HasSlug;
    use HasTranslations;

    public array $translatable = ['name', 'description'];

    /**
     * Get inclusions for current locale.
     * Supports old format (flat list) and new format (per-locale).
     */
    public function getInclusionsAttribute($value): array
    {
        $decoded = $value ? json_decode($value, true) : [];

        if (! \is_array($decoded)) {
            return [];
        }

        // Old format: flat list
        if (array_is_list($decoded)) {
            return $decoded;
        }

        // New format: per-locale
        $locale = app()->getLocale();

        return $decoded[$locale] ?? $decoded[config('app.fallback_locale')] ?? [];
    }

    /**
     * Set inclusions, converting flat list to per-locale format.
     */
    public function setInclusionsAttribute($value): void
    {
        if (\is_array($value) && array_is_list($value)) {
            $locale = app()->getLocale();
            $value = [$locale => $value];
        }

        $this->attributes['inclusions'] = \is_string($value)
            ? $value
            : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get exclusions for current locale.
     * Supports old format (flat list) and new format (per-locale).
     */
    public function getExclusionsAttribute($value): array
    {
        $decoded = $value ? json_decode($value, true) : [];

        if (! \is_array($decoded)) {
            return [];
        }

        // Old format: flat list
        if (array_is_list($decoded)) {
            return $decoded;
        }

        // New format: per-locale
        $locale = app()->getLocale();

        return $decoded[$locale] ?? $decoded[config('app.fallback_locale')] ?? [];
    }

    /**
     * Set exclusions, converting flat list to per-locale format.
     */
    public function setExclusionsAttribute($value): void
    {
        if (\is_array($value) && array_is_list($value)) {
            $locale = app()->getLocale();
            $value = [$locale => $value];
        }

        $this->attributes['exclusions'] = \is_string($value)
            ? $value
            : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get itinerary steps for current locale.
     * Each step: ['time' => string, 'title' => string, 'description' => string, 'activities' => string[]].
     */
    public function getItineraryAttribute($value): array
    {
        $decoded = $value ? json_decode($value, true) : [];

        if (! \is_array($decoded)) {
            return [];
        }

        $locale = app()->getLocale();

        return $decoded[$locale] ?? $decoded[config('app.fallback_locale')] ?? [];
    }

    /**
     * Set itinerary steps, keyed by locale.
     */
    public function setItineraryAttribute($value): void
    {
        $this->attributes['itinerary'] = \is_string($value)
            ? $value
            : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Get raw itinerary steps for a specific locale (admin forms).
     */
    public function getItineraryForLocale(string $locale): array
    {
        $raw = $this->getRawOriginal('itinerary');
        $decoded = $raw ? json_decode($raw, true) : [];

        return \is_array($decoded) ? ($decoded[$locale] ?? []) : [];
    }

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
     * Whether this is an entrance-ticket product (vs a tour package).
     */
    public function isTicket(): bool
    {
        return $this->type === 'ticket';
    }

    /**
     * Whether this package includes a tour guide in its inclusions.
     */
    public function includesTourGuide(): bool
    {
        // ponytail: keyword heuristic on raw inclusions JSON (both locales);
        // add an explicit boolean column if admins ever need manual control.
        $raw = $this->getRawOriginal('inclusions');

        return \is_string($raw) && preg_match('/guide|pemandu/i', $raw) === 1;
    }
}
