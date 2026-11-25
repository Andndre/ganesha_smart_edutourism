<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('ar_models')->whereIn('cultural_object_id', DB::table('cultural_objects')->select('id'))
            ->select('cultural_object_id')->groupBy('cultural_object_id')
            ->havingRaw('COUNT(*) > 1')->pluck('cultural_object_id');
        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException('Objek budaya memiliki lebih dari satu model 3D: '.$duplicates->implode(', '));
        }

        $orphanIds = DB::table('ar_models')->leftJoin('cultural_objects', 'cultural_objects.id', '=', 'ar_models.cultural_object_id')
            ->whereNull('cultural_objects.id')->pluck('ar_models.id');
        $paths = [];
        foreach (DB::table('ar_models')->get() as $model) {
            $paths = array_merge($paths, array_values(json_decode($model->audio_narration_paths ?? '[]', true) ?? []));
            if ($orphanIds->contains($model->id)) {
                foreach (['model_3d_path', 'model_3d_usdz_path', 'thumbnail_path', 'ar_marker_patt_path'] as $column) {
                    $paths[] = $model->$column;
                }
            }
        }

        $disk = Storage::disk('local');
        $manifest = 'ar-asset-cleanup.json';
        $pending = $disk->exists($manifest) ? json_decode($disk->get($manifest), true, flags: JSON_THROW_ON_ERROR) : [];
        $paths = array_values(array_unique(array_filter(array_merge($pending, $paths))));
        if ($paths !== [] && ! $disk->put($manifest, json_encode($paths, JSON_THROW_ON_ERROR))) {
            throw new RuntimeException('Tidak dapat menyimpan daftar pembersihan aset AR.');
        }

        DB::table('ar_models')->whereIn('id', $orphanIds)->delete();

        Schema::table('ar_models', function (Blueprint $table) {
            $table->dropForeign(['cultural_object_id']);
            $table->dropForeign(['map_location_id']);
        });
        Schema::table('ar_models', function (Blueprint $table) {
            $table->unsignedBigInteger('cultural_object_id')->nullable(false)->change();
            $table->unique('cultural_object_id');
            $table->foreign('cultural_object_id')->references('id')->on('cultural_objects')->restrictOnDelete();
            $table->dropColumn(['name', 'description', 'audio_narration_paths', 'map_location_id']);
        });
    }

    /**
     * Restore the legacy schema from cultural content. Deleted orphan models and
     * discarded duplicate content cannot be restored by a schema rollback.
     */
    public function down(): void
    {
        Schema::table('ar_models', function (Blueprint $table) {
            $table->dropForeign(['cultural_object_id']);
            $table->dropUnique(['cultural_object_id']);
        });
        Schema::table('ar_models', function (Blueprint $table) {
            $table->unsignedBigInteger('cultural_object_id')->nullable()->change();
            $table->foreign('cultural_object_id')->references('id')->on('cultural_objects')->nullOnDelete();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->json('audio_narration_paths')->nullable();
            $table->foreignId('map_location_id')->nullable()->constrained()->nullOnDelete();
        });
        foreach (DB::table('ar_models')->get() as $model) {
            $object = DB::table('cultural_objects')->find($model->cultural_object_id);
            DB::table('ar_models')->where('id', $model->id)->update([
                'name' => $object?->name,
                'description' => $object?->description,
                'audio_narration_paths' => $object?->audio_narration_paths,
            ]);
        }
    }
};
