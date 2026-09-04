<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tarif awal, diambil dari struk MKP e-ticketing 2026-06-17:
     * "RETRIBUSI TIKET MASUK DEWASA WNA(x3) Rp150.000" → Rp50.000/orang,
     * "…DEWASA WNI(x1) Rp25.000" → Rp25.000/orang,
     * "BIAYA LAYANAN(x4) Rp6.000" → Rp1.500/orang.
     *
     * Golongan anak dan lansia tidak tercetak di struk itu, jadi diseed
     * nonaktif berharga 0 — bukan angka karangan. Admin menghidupkannya lewat
     * /admin/ticket-rates begitu daftar tarif resminya turun.
     *
     * @var list<array{0: string, 1: string, 2: int, 3: int, 4: int, 5: bool}>
     */
    private const SEED_RATES = [
        ['domestic', 'Dewasa', 25000, 1500, 1, true],
        ['domestic', 'Anak-anak', 0, 1500, 2, false],
        ['domestic', 'Lansia', 0, 1500, 3, false],
        ['foreign', 'Dewasa', 50000, 1500, 4, true],
        ['foreign', 'Anak-anak', 0, 1500, 5, false],
        ['foreign', 'Lansia', 0, 1500, 6, false],
    ];

    public function up(): void
    {
        Schema::create('ticket_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('origin', ['domestic', 'foreign']);
            $table->string('name');
            $table->unsignedInteger('price')->default(0);
            // Struk MKP menagih biaya layanan per orang, terpisah dari retribusi.
            // Disimpan per golongan supaya tarif WNA bisa beda tanpa kolom baru.
            $table->unsignedInteger('service_fee')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Satu golongan per asal: mencegah dua baris "Dewasa" untuk WNI yang
            // membuat petugas harus menebak mana yang benar saat scan.
            $table->unique(['origin', 'name']);
        });

        $now = now();
        DB::table('ticket_rates')->insert(array_map(fn (array $rate): array => [
            'origin' => $rate[0],
            'name' => $rate[1],
            'price' => $rate[2],
            'service_fee' => $rate[3],
            'sort_order' => $rate[4],
            'is_active' => $rate[5],
            'created_at' => $now,
            'updated_at' => $now,
        ], self::SEED_RATES));

        Schema::create('ticket_scan_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_scan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_rate_id')->nullable()->constrained()->nullOnDelete();
            // Asal, label dan harga disalin saat scan: tarif boleh naik atau
            // dihapus nanti tanpa menulis ulang riwayat kunjungan yang lama.
            $table->enum('origin', ['domestic', 'foreign']);
            $table->string('label');
            $table->unsignedInteger('unit_price')->default(0);
            $table->unsignedInteger('unit_fee')->default(0);
            $table->unsignedInteger('quantity');
            // Retribusi saja (qty × unit_price), sejajar dengan baris "Detail
            // Produk" di struk. Biaya layanan dijumlah terpisah, seperti struknya.
            $table->unsignedInteger('subtotal')->default(0);
            $table->timestamps();

            $table->index('origin');
        });

        Schema::table('ticket_scans', function (Blueprint $table) {
            $table->unsignedInteger('total_price')->default(0)->after('party_size');
        });

        // Riwayat lama hanya menyimpan satu asal per tiket. Pindahkan apa adanya
        // ke satu baris rincian supaya statistik domestik/mancanegara yang sudah
        // terkumpul tidak hilang saat kolom origin dilepas di bawah.
        DB::table('ticket_scans')->orderBy('id')->chunk(200, function ($scans) {
            DB::table('ticket_scan_lines')->insert($scans->map(fn ($scan): array => [
                'ticket_scan_id' => $scan->id,
                'ticket_rate_id' => null,
                'origin' => $scan->origin,
                'label' => 'Tanpa golongan',
                'unit_price' => 0,
                'unit_fee' => 0,
                'quantity' => $scan->party_size,
                'subtotal' => 0,
                'created_at' => $scan->created_at,
                'updated_at' => $scan->updated_at,
            ])->all());
        });

        Schema::table('ticket_scans', function (Blueprint $table) {
            $table->dropIndex(['origin']);
            $table->dropColumn('origin');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_scans', function (Blueprint $table) {
            $table->enum('origin', ['domestic', 'foreign'])->default('domestic')->after('party_size');
            $table->index('origin');
        });

        // Tiket campuran tidak punya satu asal; ambil golongan terbanyak.
        DB::table('ticket_scan_lines')
            ->select('ticket_scan_id', 'origin')
            ->orderByDesc('quantity')
            ->get()
            ->unique('ticket_scan_id')
            ->each(fn ($line) => DB::table('ticket_scans')
                ->where('id', $line->ticket_scan_id)
                ->update(['origin' => $line->origin]));

        Schema::table('ticket_scans', function (Blueprint $table) {
            $table->dropColumn('total_price');
        });

        Schema::dropIfExists('ticket_scan_lines');
        Schema::dropIfExists('ticket_rates');
    }
};
