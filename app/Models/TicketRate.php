<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu golongan tiket beserta harganya, mis. "WNI — Dewasa, Rp 25.000".
 */
#[Fillable(['origin', 'name', 'price', 'service_fee', 'sort_order', 'is_active'])]
class TicketRate extends Model
{
    use HasFactory;

    /**
     * Label asal pengunjung untuk dashboard (Indonesia, tanpa __()).
     */
    public const ORIGIN_LABELS = [
        'domestic' => 'WNI',
        'foreign' => 'WNA',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'service_fee' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Hanya tarif yang masih dipakai petugas di gerbang.
     *
     * @param  Builder<TicketRate>  $query
     * @return Builder<TicketRate>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Urutan tampil di form scan: WNI dulu, lalu sesuai sort_order.
     *
     * @param  Builder<TicketRate>  $query
     * @return Builder<TicketRate>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByRaw("case when origin = 'domestic' then 0 else 1 end")
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function originLabel(): string
    {
        return self::ORIGIN_LABELS[$this->origin] ?? $this->origin;
    }
}
