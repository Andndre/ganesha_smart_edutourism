<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bersihkan titik rute yang locationable-nya sudah tidak ada (objek budaya/fasilitas/UMKM
     * terlanjur dihapus sebelum cascade di HasMapLocation ada). Titik seperti ini muncul di
     * halaman rute aktif sebagai "Titik Perhentian" tanpa koordinat: tombol kedatangan tidak
     * pernah terbuka, jadi wisatawan mentok di sana dan rutenya tidak bisa diselesaikan.
     */
    public function up(): void
    {
        DB::table('tour_route_points')
            ->whereNull('locationable_type')
            ->orWhereNull('locationable_id')
            ->delete();

        $types = DB::table('tour_route_points')->distinct()->pluck('locationable_type');

        foreach ($types as $type) {
            if (! $type || ! class_exists($type)) {
                continue;
            }

            $table = (new $type)->getTable();

            DB::table('tour_route_points')
                ->where('locationable_type', $type)
                ->whereNotIn('locationable_id', fn ($q) => $q->select('id')->from($table))
                ->delete();
        }
    }

    public function down(): void
    {
        // Data hilang; tidak ada yang bisa dipulihkan.
    }
};
