<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create ar_models table
        Schema::create('ar_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('model_3d_path')->nullable();
            $table->string('model_3d_usdz_path')->nullable();
            $table->string('audio_narration_path')->nullable();
            $table->timestamps();
        });

        // 2. Create ar_markers table
        Schema::create('ar_markers', function (Blueprint $table) {
            $table->id();
            $table->string('ar_marker_id')->unique();
            $table->string('ar_marker_patt_path')->nullable();
            $table->foreignId('ar_model_id')->nullable()->constrained('ar_models')->cascadeOnDelete();
            $table->foreignId('map_location_id')->nullable()->constrained('map_locations')->cascadeOnDelete();
            $table->timestamps();
        });

        // 3. Migrate existing data
        $culturalObjects = DB::table('cultural_objects')->get();
        foreach ($culturalObjects as $co) {
            $arModelId = null;

            // If it has a 3D model, create an ArModel
            if (! empty($co->model_3d_path)) {
                $arModelId = DB::table('ar_models')->insertGetId([
                    'name' => $co->name.' Model',
                    'description' => $co->description,
                    'model_3d_path' => $co->model_3d_path,
                    'model_3d_usdz_path' => $co->model_3d_usdz_path ?? null,
                    'audio_narration_path' => $co->audio_narration_path ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // If it has a marker, create an ArMarker
            if (! empty($co->ar_marker_id)) {
                // Find map location
                $mapLocation = DB::table('map_locations')
                    ->where('locationable_type', 'App\\Models\\CulturalObject')
                    ->where('locationable_id', $co->id)
                    ->first();

                DB::table('ar_markers')->insert([
                    'ar_marker_id' => $co->ar_marker_id,
                    'ar_marker_patt_path' => $co->ar_marker_patt_path ?? null,
                    'ar_model_id' => $arModelId,
                    'map_location_id' => $mapLocation ? $mapLocation->id : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $umkmProfiles = DB::table('umkm_profiles')->get();
        foreach ($umkmProfiles as $up) {
            if (! empty($up->ar_marker_id)) {
                // Find map location
                $mapLocation = DB::table('map_locations')
                    ->where('locationable_type', 'App\\Models\\UmkmProfile')
                    ->where('locationable_id', $up->id)
                    ->first();

                DB::table('ar_markers')->insert([
                    'ar_marker_id' => $up->ar_marker_id,
                    'ar_marker_patt_path' => null,
                    'ar_model_id' => null,
                    'map_location_id' => $mapLocation ? $mapLocation->id : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Drop legacy columns
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->dropUnique(['ar_marker_id']);
            $table->dropColumn([
                'ar_marker_id',
                'ar_marker_patt_path',
                'model_3d_path',
                'model_3d_usdz_path',
                'audio_narration_path',
            ]);
        });

        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->dropUnique(['ar_marker_id']);
            $table->dropColumn('ar_marker_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add legacy columns
        Schema::table('cultural_objects', function (Blueprint $table) {
            $table->string('ar_marker_id')->nullable()->unique();
            $table->string('ar_marker_patt_path')->nullable();
            $table->string('model_3d_path')->nullable();
            $table->string('model_3d_usdz_path')->nullable();
            $table->string('audio_narration_path')->nullable();
        });

        Schema::table('umkm_profiles', function (Blueprint $table) {
            $table->string('ar_marker_id')->nullable()->unique();
        });

        // 2. Restore data from ar_markers and ar_models back to objects
        $markers = DB::table('ar_markers')->get();
        foreach ($markers as $marker) {
            $mapLocation = DB::table('map_locations')->find($marker->map_location_id);
            if (! $mapLocation) {
                continue;
            }

            $model = $marker->ar_model_id ? DB::table('ar_models')->find($marker->ar_model_id) : null;

            if ($mapLocation->locationable_type === 'App\\Models\\CulturalObject') {
                DB::table('cultural_objects')
                    ->where('id', $mapLocation->locationable_id)
                    ->update([
                        'ar_marker_id' => $marker->ar_marker_id,
                        'ar_marker_patt_path' => $marker->ar_marker_patt_path,
                        'model_3d_path' => $model ? $model->model_3d_path : null,
                        'model_3d_usdz_path' => $model ? $model->model_3d_usdz_path : null,
                        'audio_narration_path' => $model ? $model->audio_narration_path : null,
                    ]);
            } elseif ($mapLocation->locationable_type === 'App\\Models\\UmkmProfile') {
                DB::table('umkm_profiles')
                    ->where('id', $mapLocation->locationable_id)
                    ->update([
                        'ar_marker_id' => $marker->ar_marker_id,
                    ]);
            }
        }

        // 3. Drop tables
        Schema::dropIfExists('ar_markers');
        Schema::dropIfExists('ar_models');
    }
};
