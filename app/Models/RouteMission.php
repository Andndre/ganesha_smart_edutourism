<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['tour_route_point_id', 'type', 'title', 'config', 'points', 'time_limit_seconds', 'order'])]
class RouteMission extends Model
{
    use HasFactory;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['title'];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'points' => 'integer',
            'time_limit_seconds' => 'integer',
            'order' => 'integer',
        ];
    }

    public function tourRoutePoint(): BelongsTo
    {
        return $this->belongsTo(TourRoutePoint::class);
    }

    /**
     * Config with every nested {en, id} leaf resolved to the current locale.
     * The config JSON mixes structural keys and translatable strings, so Spatie
     * HasTranslations can't be used on it directly.
     */
    public function localizedConfig(): array
    {
        return $this->localizeArray($this->config ?? []);
    }

    private function localizeArray(array $value): array
    {
        if (array_key_exists('en', $value) || array_key_exists('id', $value)) {
            // Locale-keyed leaf only when all values are strings (avoids
            // swallowing structural arrays that happen to have an 'id' key).
            $localeKeys = array_intersect_key($value, ['en' => 1, 'id' => 1]);
            if (\count($localeKeys) === \count($value) && ! array_filter($value, 'is_array')) {
                return ['__resolved' => translateValue($value)];
            }
        }

        foreach ($value as $key => $item) {
            if (\is_array($item)) {
                $resolved = $this->localizeArray($item);
                $value[$key] = array_key_exists('__resolved', $resolved) ? $resolved['__resolved'] : $resolved;
            }
        }

        return $value;
    }
}
