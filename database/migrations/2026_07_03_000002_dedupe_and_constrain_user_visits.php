<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateGroups = DB::table('user_visits')
            ->select('user_id', 'visitable_type', 'visitable_id')
            ->groupBy('user_id', 'visitable_type', 'visitable_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicateGroups as $group) {
            $ids = DB::table('user_visits')
                ->where('user_id', $group->user_id)
                ->where('visitable_type', $group->visitable_type)
                ->where('visitable_id', $group->visitable_id)
                ->orderByDesc('id')
                ->pluck('id');

            DB::table('user_visits')->whereIn('id', $ids->slice(1))->delete();
        }

        Schema::table('user_visits', function (Blueprint $table) {
            $table->unique(['user_id', 'visitable_type', 'visitable_id'], 'user_visits_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_visits', function (Blueprint $table) {
            $table->dropUnique('user_visits_unique');
        });
    }
};
