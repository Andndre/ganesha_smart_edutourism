<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
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
     * Config with every nested {en, id} leaf resolved to the current locale
     * and any asset paths (images, audio) normalized.
     * The config JSON mixes structural keys and translatable strings, so Spatie
     * HasTranslations can't be used on it directly.
     */
    public function localizedConfig(): array
    {
        $config = $this->localizeArray($this->config ?? []);

        return $this->resolveConfigAssets($config);
    }

    private function resolveConfigAssets(array $value): array
    {
        foreach ($value as $key => $item) {
            if (\is_array($item)) {
                $value[$key] = $this->resolveConfigAssets($item);
            } elseif (\is_string($item) && in_array($key, ['image', 'image_after', 'audio'], true)) {
                $value[$key] = self::resolveAssetUrl($item);
            }
        }

        return $value;
    }

    /**
     * Resolve a mission asset path or URL, handling WebP conversion fallbacks.
     */
    public static function resolveAssetUrl(?string $pathOrUrl): ?string
    {
        if (! $pathOrUrl) {
            return null;
        }

        // If it's an external URL not hosted on our public storage, return as is
        if (filter_var($pathOrUrl, FILTER_VALIDATE_URL)) {
            $parsedPath = parse_url($pathOrUrl, PHP_URL_PATH);
            if (! $parsedPath || ! str_contains($parsedPath, '/storage/')) {
                return $pathOrUrl;
            }
            $relative = ltrim(substr($parsedPath, strpos($parsedPath, '/storage/') + strlen('/storage/')), '/');
        } else {
            $relative = ltrim(preg_replace('#^/?(storage/)?#', '', $pathOrUrl), '/');
        }

        $disk = Storage::disk('public');

        // 1. If exact file exists on public disk
        if ($disk->exists($relative)) {
            return $disk->url($relative);
        }

        // 2. If raster image, check if webp version exists
        $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            $dir = pathinfo($relative, PATHINFO_DIRNAME);
            $filename = pathinfo($relative, PATHINFO_FILENAME);
            $webpRelative = ($dir === '.' ? '' : $dir.'/').$filename.'.webp';

            if ($disk->exists($webpRelative)) {
                return $disk->url($webpRelative);
            }
        }

        return filter_var($pathOrUrl, FILTER_VALIDATE_URL) ? $pathOrUrl : $disk->url($relative);
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
