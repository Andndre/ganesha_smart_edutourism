@extends('layouts.dashboard')

@section('title', 'Riwayat Kunjungan')

@section('content')

<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Riwayat & Statistik Kunjungan</h1>
        <p class="mt-0.5 text-sm text-gray-500">Rekap tiket yang dipindai petugas gerbang.</p>
    </div>
    <a href="{{ route('staff.ticketing') }}" class="inline-flex min-h-[44px] items-center gap-2 rounded-xl bg-primary px-4 text-sm font-semibold text-white shadow-lg shadow-primary/20 active:scale-[0.98]">
        Buka Scanner
    </a>
</div>

<form method="GET" class="mb-6 flex flex-wrap items-end gap-3">
    <div class="flex gap-2">
        @foreach (['today' => 'Hari Ini', 'month' => '30 Hari', 'custom' => 'Kustom'] as $key => $label)
            <a href="{{ route('staff.ticketing.stats', ['preset' => $key]) }}"
               class="min-h-[44px] inline-flex items-center rounded-xl px-4 text-sm font-semibold {{ $preset === $key ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
    @if ($preset === 'custom')
        <input type="hidden" name="preset" value="custom">
        <input type="date" name="start_date" value="{{ $startDate }}" class="min-h-[44px] rounded-xl border border-gray-200 px-3 text-sm">
        <input type="date" name="end_date" value="{{ $endDate }}" class="min-h-[44px] rounded-xl border border-gray-200 px-3 text-sm">
        <button type="submit" class="min-h-[44px] rounded-xl bg-primary px-4 text-sm font-semibold text-white">Terapkan</button>
    @endif
</form>

<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
    @foreach ([
        ['label' => 'Total Pengunjung', 'value' => number_format($totalVisitors, 0, ',', '.'), 'unit' => 'orang'],
        ['label' => 'Tiket Dipindai', 'value' => number_format($totalTickets, 0, ',', '.'), 'unit' => 'tiket'],
        ['label' => 'WNI', 'value' => number_format($domesticVisitors, 0, ',', '.'), 'unit' => 'orang'],
        ['label' => 'WNA', 'value' => number_format($foreignVisitors, 0, ',', '.'), 'unit' => 'orang'],
        ['label' => 'Nilai Tiket', 'value' => 'Rp '.number_format($totalRevenue, 0, ',', '.'), 'unit' => 'retribusi + layanan'],
    ] as $card)
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ $card['label'] }}</p>
            <p class="mt-2 text-2xl font-bold text-charcoal tabular-nums">{{ $card['value'] }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $card['unit'] }}</p>
        </div>
    @endforeach
</div>

@if (! empty($categories))
    <div class="mb-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <h3 class="border-b border-gray-100 px-5 py-3.5 font-semibold text-charcoal">Rincian per Golongan</h3>
        <div class="divide-y divide-gray-50">
            @foreach ($categories as $category)
                <div class="flex items-baseline justify-between gap-4 px-5 py-3 text-sm">
                    <span class="text-charcoal">{{ $category['label'] }}</span>
                    <span class="shrink-0 text-gray-500 tabular-nums">
                        {{ number_format($category['visitors'], 0, ',', '.') }} orang
                        <span class="ml-3 font-semibold text-charcoal">Rp {{ number_format($category['revenue'], 0, ',', '.') }}</span>
                    </span>
                </div>
            @endforeach
        </div>
        <p class="border-t border-gray-100 px-5 py-2.5 text-xs text-gray-500">
            Nominal di sini retribusi saja, belum termasuk biaya layanan.
        </p>
    </div>
@endif

<div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="mb-4 font-semibold text-charcoal">Sebaran per Jam</h3>
    @php $peak = max(1, max($hourly)); @endphp
    <div class="flex items-end gap-1" style="height: 120px">
        @foreach ($hourly as $hour => $count)
            <div class="flex-1" role="img"
                aria-label="Pukul {{ sprintf('%02d:00', $hour) }}: {{ $count }} orang"
                title="{{ sprintf('%02d:00', $hour) }} — {{ $count }} orang">
                <div class="rounded-t bg-primary/70" style="height: {{ (int) round($count / $peak * 110) }}px"></div>
            </div>
        @endforeach
    </div>
    <div class="mt-2 flex justify-between text-[11px] text-gray-500">
        <span>00</span><span>06</span><span>12</span><span>18</span><span>23</span>
    </div>
</div>

{{-- overflow-x-auto: enam kolom tidak muat di layar ponsel petugas, jadi tabel digeser, bukan dipotong. --}}
<div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-500">
            <tr>
                <th class="px-4 py-3">Waktu</th>
                <th class="px-4 py-3">Pengunjung</th>
                <th class="px-4 py-3">Orang</th>
                <th class="px-4 py-3">Golongan</th>
                <th class="px-4 py-3">Nilai</th>
                <th class="px-4 py-3">Petugas</th>
                <th class="px-4 py-3">Dobel</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($scans as $scan)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $scan['scanned_at'] }}</td>
                    <td class="px-4 py-3">{{ $scan['visitor_name'] }}</td>
                    <td class="px-4 py-3 tabular-nums">{{ $scan['party_size'] }}</td>
                    <td class="px-4 py-3">{{ $scan['breakdown'] ?: '-' }}</td>
                    <td class="px-4 py-3 whitespace-nowrap tabular-nums">Rp {{ number_format($scan['total_price'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3">{{ $scan['scanner_name'] }}</td>
                    <td class="px-4 py-3">{{ $scan['duplicate_attempts'] > 0 ? $scan['duplicate_attempts'].'x' : '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada tiket yang dipindai pada rentang ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($historyTruncated)
    <p class="mt-3 text-center text-xs text-gray-500">Hanya 200 tiket terbaru yang ditampilkan pada tabel ini. Kartu ringkasan di atas tetap menghitung seluruh rentang.</p>
@endif

@endsection
