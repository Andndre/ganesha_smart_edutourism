<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\TicketRate;
use App\Models\TicketScan;
use App\Models\TicketScanLine;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TicketScanController extends Controller
{
    /**
     * Halaman pemindai QR tiket.
     */
    public function index(): View
    {
        $rates = TicketRate::active()->ordered()->get();

        return view('staff.ticketing.scan', compact('rates'));
    }

    /**
     * Cek apakah kode tiket sudah pernah dicatat.
     *
     * Dipanggil segera setelah kamera membaca QR, sebelum petugas mengisi
     * jumlah orang — supaya tiket dobel ketahuan tanpa membuang input.
     */
    public function check(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'raw_code' => ['required', 'string', 'max:2048'],
        ]);

        $existing = TicketScan::with(['scanner', 'lines'])
            ->where('code_hash', TicketScan::hashCode($validated['raw_code']))
            ->first();

        if (! $existing) {
            return response()->json(['status' => 'new']);
        }

        $existing->increment('duplicate_attempts');
        $existing->update(['last_attempt_at' => now()]);

        return response()->json([
            'status' => 'duplicate',
            'scan' => $this->presentScan($existing->fresh(['scanner', 'lines'])),
        ]);
    }

    /**
     * Catat satu kunjungan baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'raw_code' => ['required', 'string', 'max:2048'],
            'visitor_name' => ['nullable', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.rate_id' => ['required', 'integer', 'exists:ticket_rates,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:200'],
        ]);

        // Harga diambil ulang dari DB, tidak dari payload: form petugas berjalan
        // di browser yang bisa diubah siapa saja, dan nilai kunjungan ikut masuk
        // laporan pendapatan.
        $rates = TicketRate::whereIn('id', array_column($validated['lines'], 'rate_id'))
            ->get()
            ->keyBy('id');

        $lines = [];
        $partySize = 0;
        $total = 0;

        foreach ($validated['lines'] as $line) {
            $rate = $rates->get($line['rate_id']);
            $quantity = (int) $line['quantity'];
            $subtotal = $rate->price * $quantity;

            $lines[] = [
                'ticket_rate_id' => $rate->id,
                'origin' => $rate->origin,
                'label' => $rate->name,
                'unit_price' => $rate->price,
                'unit_fee' => $rate->service_fee,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];

            $partySize += $quantity;
            $total += $subtotal + ($rate->service_fee * $quantity);
        }

        if ($partySize > 200) {
            throw ValidationException::withMessages([
                'lines' => 'Satu tiket maksimal 200 orang.',
            ]);
        }

        $codeHash = TicketScan::hashCode($validated['raw_code']);

        try {
            return DB::transaction(function () use ($validated, $codeHash, $lines, $partySize, $total) {
                $existing = TicketScan::with(['scanner', 'lines'])
                    ->where('code_hash', $codeHash)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    return $this->duplicateResponse($existing);
                }

                $scan = TicketScan::create([
                    'code_hash' => $codeHash,
                    'raw_code' => trim($validated['raw_code']),
                    'visitor_name' => $validated['visitor_name'] ?? null,
                    'party_size' => $partySize,
                    'total_price' => $total,
                    'scanned_at' => now(),
                    'scanned_by' => auth()->id(),
                ]);

                $scan->lines()->createMany($lines);

                return response()->json([
                    'success' => true,
                    'scan' => $this->presentScan($scan->load('scanner', 'lines')),
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            // Petugas lain menang balapan setelah lock kita lepas: perlakukan
            // sama seperti duplikat biasa, bukan kegagalan server.
            $winner = TicketScan::with(['scanner', 'lines'])->where('code_hash', $codeHash)->first();

            if (! $winner) {
                throw new \RuntimeException('Tiket dobel terdeteksi tetapi barisnya tidak ditemukan.');
            }

            return $this->duplicateResponse($winner);
        }
    }

    /**
     * Catat percobaan ulang dan balas dengan detail scan pertama.
     */
    private function duplicateResponse(TicketScan $existing): JsonResponse
    {
        $existing->increment('duplicate_attempts');
        $existing->update(['last_attempt_at' => now()]);

        return response()->json([
            'success' => false,
            'status' => 'duplicate',
            'scan' => $this->presentScan($existing->fresh(['scanner', 'lines'])),
        ], 409);
    }

    /**
     * Jumlah baris riwayat yang dirender di halaman statistik.
     */
    private const HISTORY_LIMIT = 200;

    /**
     * Riwayat dan statistik kunjungan dari hasil scan.
     */
    public function stats(Request $request): View
    {
        $validated = $request->validate([
            'preset' => ['nullable', 'string', 'in:today,month,custom'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $preset = $validated['preset'] ?? 'today';
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;

        if ($preset === 'custom') {
            $startDate = $startDate ?: today()->subDays(7)->format('Y-m-d');
            $endDate = $endDate ?: today()->format('Y-m-d');
            $from = Carbon::parse($startDate)->startOfDay();
            $to = Carbon::parse($endDate)->endOfDay();

            // Tanggal yang terisi otomatis bisa melewati tanggal akhir yang
            // dikirim petugas (mis. hanya end_date yang diisi), dan aturan
            // after_or_equal di atas tidak menjangkau nilai default itu.
            if ($from->greaterThan($to)) {
                throw ValidationException::withMessages([
                    'end_date' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
                ]);
            }
        } elseif ($preset === 'month') {
            $from = today()->subDays(30)->startOfDay();
            $to = today()->endOfDay();
        } else {
            $preset = 'today';
            $from = today()->startOfDay();
            $to = today()->endOfDay();
        }

        // Agregat dihitung di SQL supaya rentang panjang (mis. 30 hari) tidak
        // perlu menghidrasi seluruh baris ke PHP hanya untuk dijumlahkan.
        $totalVisitors = (int) TicketScan::whereBetween('scanned_at', [$from, $to])->sum('party_size');
        $totalTickets = TicketScan::whereBetween('scanned_at', [$from, $to])->count();
        $totalRevenue = (int) TicketScan::whereBetween('scanned_at', [$from, $to])->sum('total_price');

        // Satu tiket boleh berisi campuran WNI dan WNA, jadi asal pengunjung
        // dijumlah dari rincian golongan, bukan dari satu kolom di tiket.
        $categoryTotals = TicketScanLine::query()
            ->whereHas('scan', fn ($query) => $query->whereBetween('scanned_at', [$from, $to]))
            ->selectRaw('origin, label, sum(quantity) as visitors, sum(subtotal) as revenue')
            ->groupBy('origin', 'label')
            ->orderByRaw("case when origin = 'domestic' then 0 else 1 end")
            ->orderByDesc('visitors')
            ->get();

        $domesticVisitors = (int) $categoryTotals->where('origin', 'domestic')->sum('visitors');
        $foreignVisitors = (int) $categoryTotals->where('origin', 'foreign')->sum('visitors');

        $categories = $categoryTotals->map(fn ($row) => [
            'label' => TicketRate::ORIGIN_LABELS[$row->origin].' — '.$row->label,
            'visitors' => (int) $row->visitors,
            'revenue' => (int) $row->revenue,
        ])->all();

        // Proyeksi sempit (hanya kolom yang dipakai) untuk sebaran per jam,
        // tetap menghidrasi semua baris di rentang tapi tanpa relasi/kolom lain.
        $hourly = array_fill(0, 24, 0);
        TicketScan::whereBetween('scanned_at', [$from, $to])
            ->select('scanned_at', 'party_size')
            ->orderBy('scanned_at')
            ->get()
            ->each(function (TicketScan $record) use (&$hourly) {
                $hourly[(int) $record->scanned_at->format('G')] += $record->party_size;
            });

        // Tabel riwayat dibatasi ke baris terbaru saja agar halaman tetap ringan.
        $scanRecords = TicketScan::with(['scanner', 'lines'])
            ->whereBetween('scanned_at', [$from, $to])
            ->orderByDesc('scanned_at')
            ->limit(self::HISTORY_LIMIT)
            ->get();

        $historyTruncated = $totalTickets > self::HISTORY_LIMIT;

        $scans = $scanRecords->map(fn (TicketScan $record) => [
            'visitor_name' => $record->visitor_name ?: 'Pengunjung',
            'party_size' => $record->party_size,
            'total_price' => $record->total_price,
            'breakdown' => $record->lines
                ->map(fn ($line) => $line->quantity.' '.TicketRate::ORIGIN_LABELS[$line->origin].' '.$line->label)
                ->implode(', '),
            'scanned_at' => $record->scanned_at->format('d/m/Y H:i'),
            'scanner_name' => $record->scanner->name ?? 'Petugas',
            'duplicate_attempts' => $record->duplicate_attempts,
        ])->values()->toArray();

        return view('staff.ticketing.stats', compact(
            'preset', 'startDate', 'endDate',
            'totalVisitors', 'totalTickets', 'totalRevenue',
            'domesticVisitors', 'foreignVisitors', 'categories',
            'hourly', 'scans', 'historyTruncated'
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function presentScan(TicketScan $scan): array
    {
        return [
            'visitor_name' => $scan->visitor_name ?: 'Pengunjung',
            'party_size' => $scan->party_size,
            'total_price' => $scan->total_price,
            'breakdown' => $scan->lines->map(fn ($line) => [
                'label' => TicketRate::ORIGIN_LABELS[$line->origin].' '.$line->label,
                'quantity' => $line->quantity,
                'subtotal' => $line->subtotal,
            ])->values()->all(),
            'scanned_at' => $scan->scanned_at->format('H:i'),
            'scanned_at_date' => $scan->scanned_at->format('d/m/Y'),
            'scanner_name' => $scan->scanner->name ?? 'Petugas',
            'duplicate_attempts' => $scan->duplicate_attempts,
        ];
    }
}
