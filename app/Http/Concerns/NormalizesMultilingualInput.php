<?php

namespace App\Http\Concerns;

use Illuminate\Http\Request;

trait NormalizesMultilingualInput
{
    /**
     * Normalize a single translatable field from string to [en, id] array.
     * No-op if already an array. Defaults to $this when called from a FormRequest;
     * pass $request explicitly when called from a controller.
     */
    protected function normalizeLocaleField(string $field, ?Request $request = null): void
    {
        $request ??= $this;
        if ($request->has($field) && \is_string($request->input($field))) {
            $request->merge([
                $field => [
                    'en' => $request->input($field),
                    'id' => $request->input($field),
                ],
            ]);
        }
    }

    /**
     * Normalize multiple fields at once.
     */
    protected function normalizeLocaleFields(array $fields, ?Request $request = null): void
    {
        foreach ($fields as $field) {
            $this->normalizeLocaleField($field, $request);
        }
    }

    /**
     * Normalize array-of-locales fields (e.g. story_title, story_content, quiz_question).
     * Each item can be a string (duped to en/id) or already an array.
     */
    protected function normalizeLocaleArrayField(string $field, ?Request $request = null): void
    {
        $request ??= $this;
        if (! $request->has($field) || ! \is_array($request->input($field))) {
            return;
        }

        $items = $request->input($field);
        $normalized = [];
        foreach ($items as $val) {
            $normalized[] = \is_array($val) ? $val : ['en' => (string) $val, 'id' => (string) $val];
        }
        $request->merge([$field => $normalized]);
    }
}
