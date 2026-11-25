<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class UnusedArAssetCleaner
{
    public const MANIFEST = 'ar-asset-cleanup.json';

    /** @param array<int, string|null> $paths */
    public function deleteUnused(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            if (str_starts_with($path, '/') || str_contains($path, '..') || str_contains($path, ':') || str_contains($path, '\\') || str_contains($path, "\0") || $this->isReferenced($path)) {
                continue;
            }

            if (! Storage::disk('public')->delete($path)) {
                throw new \RuntimeException('Gagal membersihkan aset AR: '.$path);
            }
        }
    }

    private function isReferenced(string $path): bool
    {
        if (DB::table('ar_models')->where(function ($query) use ($path) {
            foreach (['model_3d_path', 'model_3d_usdz_path', 'thumbnail_path', 'ar_marker_patt_path'] as $column) {
                $query->orWhere($column, $path);
            }
        })->exists()) {
            return true;
        }

        foreach (DB::table('cultural_objects')->select('audio_narration_paths', 'historical_images')->cursor() as $object) {
            if (in_array($path, json_decode($object->audio_narration_paths ?? '[]', true) ?? [], true)
                || in_array($path, json_decode($object->historical_images ?? '[]', true) ?? [], true)) {
                return true;
            }
        }

        if (Schema::hasColumn('ar_models', 'audio_narration_paths')) {
            foreach (DB::table('ar_models')->whereNotNull('audio_narration_paths')->pluck('audio_narration_paths') as $audio) {
                if (in_array($path, json_decode($audio, true) ?? [], true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
