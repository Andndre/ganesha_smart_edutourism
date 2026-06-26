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
        $objects = Cache::tags(['cultural'])->flexible("cultural_objects_all_array_{$locale}", [3600, 7200], function () {
            $models = CulturalObject::with('mapLocation.arModel')
                ->orderBy('name->'.app()->getLocale())
                ->get()
                ->append(['ar_marker_id', 'model_3d_path', 'audio_narration_path']);

            return $models->map(function ($model) {
                $data = $model->toArray();
                $locale = app()->getLocale();
                foreach (['name', 'short_description', 'description'] as $field) {
                    if (isset($data[$field]) && \is_array($data[$field])) {
                        $data[$field] = $data[$field][$locale] ?? $data[$field][config('app.fallback_locale')] ?? reset($data[$field]) ?? '';
                    }
                }

                return $data;
            })->values()->toArray();
        });

        return view('user.cultural.index', compact('objects'));
    }

    /**
     * Display the specified cultural object with its stories.
     */
    public function show(string $slug): View
    {
        $locale = app()->getLocale();
        $object = Cache::tags(['cultural'])->flexible("cultural_object_array_{$slug}_{$locale}", [3600, 7200], function () use ($slug, $locale) {
            $model = CulturalObject::with(['stories', 'mapLocation.arModel'])
                ->where('slug', $slug)
                ->firstOrFail()
                ->append(['ar_marker_id', 'model_3d_path', 'audio_narration_path', 'model_3d_usdz_path', 'ar_marker_patt_path']);

            $data = $model->toArray();

            // Resolve translatable fields to locale-specific strings
            $locale = app()->getLocale();
            foreach (['name', 'short_description', 'description'] as $field) {
                if (isset($data[$field]) && \is_array($data[$field])) {
                    $data[$field] = $data[$field][$locale] ?? $data[$field][config('app.fallback_locale')] ?? reset($data[$field]) ?? '';
                }
            }

            // Resolve translatable fields in nested stories
            if (isset($data['stories']) && \is_array($data['stories'])) {
                foreach ($data['stories'] as $i => $story) {
                    foreach (['title', 'content'] as $sf) {
                        if (isset($story[$sf]) && \is_array($story[$sf])) {
                            $data['stories'][$i][$sf] = $story[$sf][$locale] ?? $story[$sf][config('app.fallback_locale')] ?? reset($story[$sf]) ?? '';
                        }
                    }
                }
            }

            return $data;
        });

        return view('user.cultural.show', compact('object'));
    }
}
