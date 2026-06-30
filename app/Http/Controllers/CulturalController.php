<?php

namespace App\Http\Controllers;

use App\Models\CulturalObject;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class CulturalController extends Controller
{
    /**
     * Display a listing of the cultural objects.
     */
    public function index(): View
    {
        $locale = app()->getLocale();
        $objects = Cache::tags(['cultural'])->flexible("cultural_objects_all_array_$locale", [3600, 7200], function () {
            $models = CulturalObject::with('mapLocation.arModel')
                ->orderBy('name->'.app()->getLocale())
                ->get()
                ->append(['ar_marker_id', 'model_3d_path', 'audio_narration_path']);

            return $models->map(function ($model) {
                list($data,) = $this->resolveTrans($model);

                return $data;
            })->values()->toArray();
        });

        return view('user.cultural.index', compact('objects'));
    }

    /**
     * Display the specified cultural object.
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $object = Cache::tags(['cultural'])->flexible("cultural_object_array_{$slug}_$locale", [3600, 7200], function () use ($slug, $locale) {
            $model = CulturalObject::with('mapLocation.arModel')
                ->where('slug', $slug)
                ->firstOrFail()
                ->append(['ar_marker_id', 'model_3d_path', 'audio_narration_path', 'model_3d_usdz_path', 'ar_marker_patt_path']);

            list($data, $locale) = $this->resolveTrans($model);

            // Override audio_narration_path dengan versi locale-spesifik jika tersedia
            if (!empty($data['audio_narration_paths']) && is_array($data['audio_narration_paths'])) {
                $localePath = $data['audio_narration_paths'][$locale]
                    ?? $data['audio_narration_paths'][config('app.fallback_locale', 'en')]
                    ?? null;
                if ($localePath) {
                    $data['audio_narration_path'] = $localePath;
                }
            }

            return $data;
        });

        return view('user.cultural.show', compact('object'));
    }

    /**
     * @param mixed $model
     * @return array
     */
    private function resolveTrans(mixed $model): array
    {
        $data = $model->toArray();

        // Resolve translatable fields to locale-specific strings
        $locale = app()->getLocale();
        foreach (['name', 'short_description', 'description'] as $field) {
            if (isset($data[$field]) && \is_array($data[$field])) {
                $data[$field] = $data[$field][$locale] ?? $data[$field][config('app.fallback_locale')] ?? reset($data[$field]) ?? '';
            }
        }
        return array($data, $locale);
    }
}
