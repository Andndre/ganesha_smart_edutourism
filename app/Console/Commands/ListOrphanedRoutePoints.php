<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:orphaned-route-points')]
#[Description('Tampilkan titik rute yang locationable-nya sudah dihapus (dibersihkan oleh migrasi)')]
class ListOrphanedRoutePoints extends Command
{
    /**
     * Read-only: hanya melaporkan. Penghapusannya dikerjakan migrasi
     * 2026_09_04_000001_delete_orphaned_tour_route_points saat deploy.
     */
    public function handle(): int
    {
        $orphans = collect();

        $types = DB::table('tour_route_points')->distinct()->pluck('locationable_type');

        foreach ($types as $type) {
            $query = DB::table('tour_route_points as p')
                ->leftJoin('tour_routes as r', 'r.id', '=', 'p.tour_route_id')
                ->select('p.id', 'p.tour_route_id', 'p.order', 'p.locationable_type', 'p.locationable_id', 'r.name as route_name');

            if (! $type || ! class_exists($type)) {
                $orphans = $orphans->merge($query->where('p.locationable_type', $type)->get());

                continue;
            }

            $table = (new $type)->getTable();

            $orphans = $orphans->merge(
                $query->where('p.locationable_type', $type)
                    ->whereNotIn('p.locationable_id', fn ($q) => $q->select('id')->from($table))
                    ->get()
            );
        }

        if ($orphans->isEmpty()) {
            $this->info('Tidak ada titik rute yang menggantung.');

            return self::SUCCESS;
        }

        $this->table(
            ['Point ID', 'Rute', 'Urutan', 'Menunjuk ke', 'Sesi aktif di titik ini'],
            $orphans->map(fn ($p) => [
                $p->id,
                translateValue($p->route_name),
                $p->order,
                ($p->locationable_type ?? 'NULL').'#'.($p->locationable_id ?? 'NULL'),
                DB::table('route_sessions')->where('current_point_id', $p->id)->count(),
            ])
        );

        $this->warn($orphans->count().' titik akan dihapus oleh migrasi saat deploy.');

        return self::SUCCESS;
    }
}
