<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\TicketScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TicketScanController extends Controller
{
    /**
     * Halaman pemindai QR tiket.
     */
    public function index(): View
    {
        return view('staff.ticketing.scan');
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

        $existing = TicketScan::with('scanner')
            ->where('code_hash', TicketScan::hashCode($validated['raw_code']))
            ->first();

        if (! $existing) {
            return response()->json(['status' => 'new']);
        }

        $existing->increment('duplicate_attempts');
        $existing->update(['last_attempt_at' => now()]);

        return response()->json([
            'status' => 'duplicate',
            'scan' => $this->presentScan($existing->fresh('scanner')),
        ]);
    }

    /**
     * Catat satu kunjungan baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'raw_code' => ['required', 'string', 'max:2048'],
            'party_size' => ['required', 'integer', 'min:1', 'max:200'],
            'origin' => ['required', 'in:domestic,foreign'],
            'visitor_name' => ['nullable', 'string', 'max:255'],
        ]);

        $codeHash = TicketScan::hashCode($validated['raw_code']);

        return DB::transaction(function () use ($validated, $codeHash) {
            $existing = TicketScan::with('scanner')
                ->where('code_hash', $codeHash)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->increment('duplicate_attempts');
                $existing->update(['last_attempt_at' => now()]);

                return response()->json([
                    'success' => false,
                    'status' => 'duplicate',
                    'scan' => $this->presentScan($existing->fresh('scanner')),
                ], 409);
            }

            $scan = TicketScan::create([
                'code_hash' => $codeHash,
                'raw_code' => trim($validated['raw_code']),
                'visitor_name' => $validated['visitor_name'] ?? null,
                'party_size' => $validated['party_size'],
                'origin' => $validated['origin'],
                'scanned_at' => now(),
                'scanned_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'scan' => $this->presentScan($scan->load('scanner')),
            ]);
        });
    }

    /**
     * Riwayat dan statistik kunjungan dari hasil scan.
     */
    public function stats(Request $request): View
    {
        $preset = $request->query('preset', 'today');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        if ($preset === 'custom') {
            $startDate = $startDate ?: today()->subDays(7)->format('Y-m-d');
            $endDate = $endDate ?: today()->format('Y-m-d');
            $from = Carbon::parse($startDate)->startOfDay();
            $to = Carbon::parse($endDate)->endOfDay();
        } elseif ($preset === 'month') {
            $from = today()->subDays(30)->startOfDay();
            $to = today()->endOfDay();
        } else {
            $preset = 'today';
            $from = today()->startOfDay();
            $to = today()->endOfDay();
        }

        $scanRecords = TicketScan::with('scanner')
            ->whereBetween('scanned_at', [$from, $to])
            ->orderByDesc('scanned_at')
            ->get();

        $totalVisitors = (int) $scanRecords->sum('party_size');
        $totalTickets = $scanRecords->count();
        $domesticVisitors = (int) $scanRecords->where('origin', 'domestic')->sum('party_size');
        $foreignVisitors = (int) $scanRecords->where('origin', 'foreign')->sum('party_size');

        $hourly = array_fill(0, 24, 0);
        foreach ($scanRecords as $record) {
            $hourly[(int) $record->scanned_at->format('G')] += $record->party_size;
        }

        $scans = $scanRecords->map(fn (TicketScan $record) => [
            'visitor_name' => $record->visitor_name ?: 'Pengunjung',
            'party_size' => $record->party_size,
            'origin' => $record->origin === 'foreign' ? 'Asing' : 'Domestik',
            'scanned_at' => $record->scanned_at->format('d/m/Y H:i'),
            'scanner_name' => $record->scanner->name ?? 'Petugas',
            'duplicate_attempts' => $record->duplicate_attempts,
            'raw_code' => $record->raw_code,
        ])->values()->toArray();

        return view('staff.ticketing.stats', compact(
            'preset', 'startDate', 'endDate',
            'totalVisitors', 'totalTickets', 'domesticVisitors', 'foreignVisitors',
            'hourly', 'scans'
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
            'origin' => $scan->origin,
            'scanned_at' => $scan->scanned_at->format('H:i'),
            'scanned_at_date' => $scan->scanned_at->format('d/m/Y'),
            'scanner_name' => $scan->scanner->name ?? 'Petugas',
            'duplicate_attempts' => $scan->duplicate_attempts,
        ];
    }
}
