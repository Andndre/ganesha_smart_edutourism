<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
trait HasSlug
{
    /**
     * Override in model to specify which translatable field to slugify.
     */
    protected function slugSourceField(): string
    {
        return 'name';
    }

    /**
     * Generate a slug from the model's translatable name field.
     *
     * @param  string|null  $fromValue  Explicit value to slugify (bypasses reading from model)
     * @param  int|null  $excludeId  For uniqueness checks: exclude this model ID
     */
    public function generateSlug(?string $fromValue = null, ?int $excludeId = null): string
    {
        $locale = config('app.fallback_locale', 'en');

        if ($fromValue === null) {
            $source = $this->{$this->slugSourceField()};
            $fromValue =  \is_array($source)
                ? ($source[$locale] ?? $source['en'] ?? reset($source))
                : $source;
        }

        return Str::slug($fromValue);
    }

    /**
     * Generate a unique slug with a random suffix.
     */
    public function generateUniqueSlug(?string $fromValue = null): string
    {
        return $this->generateSlug($fromValue).'-'.Str::random(5);
    }

    /**
     * Generate a unique slug using while-loop collision check.
     *
     * @param  string|null  $fromValue  Explicit value to slugify
     * @param  int|null  $excludeId  Exclude this model ID from collision check
     */
    public function generateCollisionFreeSlug(?string $fromValue = null, ?int $excludeId = null): string
    {
        $baseSlug = $this->generateSlug($fromValue);
        $slug = $baseSlug;
        $count = 1;

        $query = static::where('slug', $slug);
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $baseSlug.'-'.$count++;
            $query = static::where('slug', $slug);
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }
}
