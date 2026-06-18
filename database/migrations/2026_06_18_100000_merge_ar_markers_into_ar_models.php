<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add marker columns + map_location FK to ar_models
        Schema::table('ar_models', function (Blueprint $table) {
            $table->string('ar_marker_id')->nullable()->unique()->after('name');
            $table->string('ar_marker_patt_path')->nullable()->after('ar_marker_id');
            $table->foreignId('map_location_id')->nullable()->after('ar_marker_patt_path')
                ->constrained('map_locations')->nullOnDelete();
        });

        // 2. Migrate data: copy marker fields to the linked model
        $markers = DB::table('ar_markers')->whereNotNull('ar_model_id')->get();
        foreach ($markers as $marker) {
            DB::table('ar_models')->where('id', $marker->ar_model_id)->update([
                'ar_marker_id'       => $marker->ar_marker_id,
                'ar_marker_patt_path' => $marker->ar_marker_patt_path,
                'map_location_id'    => $marker->map_location_id,
            ]);
        }

        // 3. Markers without a model: orphaned, just drop them (no model to link to)

        // 4. Drop ar_markers
        Schema::dropIfExists('ar_markers');
    }

    public function down(): void
    {
        Schema::create('ar_markers', function (Blueprint $table) {
            $table->id();
            $table->string('ar_marker_id')->unique();
            $table->string('ar_marker_patt_path')->nullable();
            $table->foreignId('ar_model_id')->nullable()->constrained('ar_models')->cascadeOnDelete();
            $table->foreignId('map_location_id')->nullable()->constrained('map_locations')->cascadeOnDelete();
            $table->timestamps();
        });

        // Restore data from ar_models back to ar_markers
        $models = DB::table('ar_models')->whereNotNull('ar_marker_id')->get();
        foreach ($models as $model) {
            DB::table('ar_markers')->insert([
                'ar_marker_id'       => $model->ar_marker_id,
                'ar_marker_patt_path' => $model->ar_marker_patt_path,
                'ar_model_id'        => $model->id,
                'map_location_id'    => $model->map_location_id,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        Schema::table('ar_models', function (Blueprint $table) {
            $table->dropForeign(['map_location_id']);
            $table->dropUnique(['ar_marker_id']);
            $table->dropColumn(['ar_marker_id', 'ar_marker_patt_path', 'map_location_id']);
        });
    }
};
