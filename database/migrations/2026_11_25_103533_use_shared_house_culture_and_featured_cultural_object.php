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
        Schema::table('village_settings', function (Blueprint $table): void {
            $table->foreignId('house_cultural_object_id')->nullable()->constrained('cultural_objects')->restrictOnDelete();
            $table->foreignId('featured_cultural_object_id')->nullable()->constrained('cultural_objects')->restrictOnDelete();
        });

        $roots = DB::table('cultural_objects')->whereNull('parent_id')->where('place_type', 'pekarangan')->pluck('id');
        if ($roots->count() !== 1) {
            $linked = DB::table('cultural_objects')
                ->whereIn('id', DB::table('cultural_object_traditional_house')->select('cultural_object_id'))
                ->get(['id', 'parent_id']);
            $roots = $linked->map(fn (object $object): int => (int) ($object->parent_id ?? $object->id))->unique()->values();
            $roots = DB::table('cultural_objects')->whereNull('parent_id')->whereIn('id', $roots)->pluck('id');
        }

        // Only reuse an unambiguous existing courtyard. Never guess by name or reparent content.
        if ($roots->count() === 1 && DB::table('cultural_objects')->where('parent_id', $roots->first())->exists()) {
            DB::table('village_settings')->where('id', 1)->update([
                'house_cultural_object_id' => $roots->first(),
                'featured_cultural_object_id' => $roots->first(),
            ]);
        }

        Schema::dropIfExists('cultural_object_traditional_house');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Per-house selections were retired; rollback recreates the table without restoring those selections.
        Schema::create('cultural_object_traditional_house', function (Blueprint $table): void {
            $table->foreignId('traditional_house_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cultural_object_id')->constrained()->cascadeOnDelete();
            $table->primary(['traditional_house_id', 'cultural_object_id']);
        });

        Schema::table('village_settings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('house_cultural_object_id');
            $table->dropConstrainedForeignId('featured_cultural_object_id');
        });
    }
};
