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
        Schema::table('ar_models', function (Blueprint $table) {
            $table->foreignId('cultural_object_id')->nullable()->after('map_location_id')
                ->constrained('cultural_objects')->nullOnDelete();
        });

        // Backfill: an AR model's cultural object is whoever owns the map location it's pinned to.
        // ponytail: map_location_id column is kept (nullable, unused after this) as a safety net for
        // one release — drop it in a follow-up once we're sure no AR model needs a non-cultural owner.
        $models = DB::table('ar_models')->whereNotNull('map_location_id')->get();
        foreach ($models as $model) {
            $mapLocation = DB::table('map_locations')->find($model->map_location_id);

            if ($mapLocation && $mapLocation->locationable_type === 'App\\Models\\CulturalObject') {
                DB::table('ar_models')->where('id', $model->id)->update([
                    'cultural_object_id' => $mapLocation->locationable_id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ar_models', function (Blueprint $table) {
            $table->dropForeign(['cultural_object_id']);
            $table->dropColumn('cultural_object_id');
        });
    }
};
