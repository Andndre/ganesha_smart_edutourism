<?php

namespace App\Models\Concerns;

trait HasTranslatableArrayOutput
{
    /**
     * Spatie's getAttributeValue() override is not called by Laravel's default
     * attributesToArray(), so translatable fields would be serialized as raw
     * JSON strings in toArray() output. Use getTranslations() to return all
     * locales as ['en' => ..., 'id' => ...] instead of a single-locale string.
     */
    public function attributesToArray(): array
    {
        $attributes = parent::attributesToArray();

        foreach ($this->getTranslatableAttributes() as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->getTranslations($key);
            }
        }

        return $attributes;
    }
}
