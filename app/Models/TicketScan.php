<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code_hash', 'raw_code', 'visitor_name', 'party_size', 'origin', 'scanned_at', 'scanned_by', 'duplicate_attempts', 'last_attempt_at'])]
class TicketScan extends Model
{
    use HasFactory;

    /**
     * Hash isi QR menjadi kunci duplikat berpanjang tetap.
     *
     * Isi QR OTA bisa berupa URL panjang yang melewati batas panjang index
     * MySQL; hash-nya selalu 64 karakter sehingga aman di-index unik.
     */
    public static function hashCode(string $rawCode): string
    {
        return hash('sha256', trim($rawCode));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'party_size' => 'integer',
            'duplicate_attempts' => 'integer',
        ];
    }

    /**
     * Petugas yang memindai tiket ini.
     *
     * @return BelongsTo<User, TicketScan>
     */
    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
