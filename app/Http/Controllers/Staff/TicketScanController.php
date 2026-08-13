<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\TicketScan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
