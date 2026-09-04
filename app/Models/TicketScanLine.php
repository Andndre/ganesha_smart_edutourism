<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu golongan di dalam satu tiket, mis. "2 × WNI Dewasa".
 *
 * Label dan harga disalin dari TicketRate saat scan, bukan dibaca lewat relasi:
 * tarif boleh naik atau dihapus tanpa mengubah nilai kunjungan yang sudah lewat.
 */
#[Fillable(['ticket_rate_id', 'origin', 'label', 'unit_price', 'unit_fee', 'quantity', 'subtotal'])]
class TicketScanLine extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'integer',
            'unit_fee' => 'integer',
            'quantity' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<TicketScan, TicketScanLine>
     */
    public function scan(): BelongsTo
    {
        return $this->belongsTo(TicketScan::class, 'ticket_scan_id');
    }

    /**
     * @return BelongsTo<TicketRate, TicketScanLine>
     */
    public function rate(): BelongsTo
    {
        return $this->belongsTo(TicketRate::class, 'ticket_rate_id');
    }
}
