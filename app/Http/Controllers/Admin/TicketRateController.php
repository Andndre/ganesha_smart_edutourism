<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TicketRate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketRateController extends Controller
{
    /**
     * Daftar tarif tiket, dikelompokkan per asal pengunjung.
     */
    public function index(): View
    {
        $rates = TicketRate::ordered()->get()->groupBy('origin');

        return view('admin.ticket-rates.index', compact('rates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        TicketRate::create($validated + ['is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('admin.ticket-rates')->with('success', 'Tarif tiket berhasil ditambahkan.');
    }

    public function update(Request $request, TicketRate $ticketRate): RedirectResponse
    {
        $validated = $request->validate($this->rules($ticketRate));

        $ticketRate->update($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.ticket-rates')->with('success', 'Tarif tiket berhasil diperbarui.');
    }

    /**
     * Hapus tarif. Rincian kunjungan yang sudah tercatat tetap utuh karena
     * label dan harganya disalin ke ticket_scan_lines saat scan.
     */
    public function destroy(TicketRate $ticketRate): RedirectResponse
    {
        $ticketRate->delete();

        return redirect()->route('admin.ticket-rates')->with('success', 'Tarif tiket berhasil dihapus.');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(?TicketRate $ignore = null): array
    {
        return [
            'origin' => ['required', Rule::in(['domestic', 'foreign'])],
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('ticket_rates', 'name')
                    ->where(fn ($query) => $query->where('origin', request('origin')))
                    ->ignore($ignore),
            ],
            // Harga disimpan dalam rupiah penuh, bukan sen: admin mengetik angka
            // yang sama dengan yang tercetak di struk.
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'service_fee' => ['required', 'integer', 'min:0', 'max:1000000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
