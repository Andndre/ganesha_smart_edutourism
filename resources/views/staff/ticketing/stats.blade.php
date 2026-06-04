@extends('layouts.dashboard')

@section('title', 'Statistik Penjualan Tiket')

@section('content')
<div class="max-w-6xl pb-12">
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-charcoal">Statistik Penjualan Tiket</h1>
            <p class="mt-0.5 text-sm text-gray-500">Laporan ringkasan penjualan tiket dan pendapatan wisata.</p>
        </div>
        <div>
            <a href="{{ route('staff.ticketing') }}" class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition-all active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke POS
            </a>
        </div>
    </div>

    <!-- Date Presets Navigation -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('staff.ticketing.stats', ['preset' => 'today']) }}" 
           class="rounded-xl px-4 py-2.5 text-xs font-semibold shadow-sm transition-all {{ $preset === 'today' ? 'bg-primary text-white shadow-primary/10' : 'bg-white border border-gray-100 text-gray-600 hover:bg-gray-50' }}">
            Hari Ini
        </a>
        <a href="{{ route('staff.ticketing.stats', ['preset' => 'month']) }}" 
           class="rounded-xl px-4 py-2.5 text-xs font-semibold shadow-sm transition-all {{ $preset === 'month' ? 'bg-primary text-white shadow-primary/10' : 'bg-white border border-gray-100 text-gray-600 hover:bg-gray-50' }}">
            1 Bulan Terakhir
        </a>
        <a href="{{ route('staff.ticketing.stats', ['preset' => 'all']) }}" 
           class="rounded-xl px-4 py-2.5 text-xs font-semibold shadow-sm transition-all {{ $preset === 'all' ? 'bg-primary text-white shadow-primary/10' : 'bg-white border border-gray-100 text-gray-600 hover:bg-gray-50' }}">
            Keseluruhan
        </a>
        <a href="{{ route('staff.ticketing.stats', ['preset' => 'custom']) }}" 
           class="rounded-xl px-4 py-2.5 text-xs font-semibold shadow-sm transition-all {{ $preset === 'custom' ? 'bg-primary text-white shadow-primary/10' : 'bg-white border border-gray-100 text-gray-600 hover:bg-gray-50' }}">
            Pilih Rentang Tanggal
        </a>
    </div>

    <!-- Custom Date Range Form -->
    @if ($preset === 'custom')
        <form action="{{ route('staff.ticketing.stats') }}" method="GET" class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm max-w-xl">
            <input type="hidden" name="preset" value="custom">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" required
                           class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none bg-white">
                </div>
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" required
                           class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none bg-white">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-xs font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                    Terapkan Filter
                </button>
            </div>
        </form>
    @endif

    <!-- Statistics Cards Grid -->
    <div class="mb-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Tiket Terjual -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tiket Terjual</p>
                <h4 class="text-xl font-bold text-charcoal mt-0.5">{{ $totalTicketsSold }} <span class="text-xs font-medium text-gray-500">Tiket</span></h4>
            </div>
        </div>

        <!-- Total Pendapatan -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m0-2h.01" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pendapatan</p>
                <h4 class="text-lg font-bold text-charcoal mt-0.5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
            </div>
        </div>

        <!-- Tunai -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tunai (Cash)</p>
                <h4 class="text-lg font-bold text-charcoal mt-0.5">Rp {{ number_format($cashRevenue, 0, ',', '.') }}</h4>
            </div>
        </div>

        <!-- QRIS -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M4 8h.01M4 16h.01M4 20h.01m1.99-16h.01M12 4h.01M16 4h.01" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">QRIS</p>
                <h4 class="text-lg font-bold text-charcoal mt-0.5">Rp {{ number_format($qrisRevenue, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    <!-- Rincian Tiket Terjual Table -->
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="font-display text-lg font-bold text-charcoal mb-4">Rincian Tiket Terjual</h3>
        
        <div class="overflow-x-auto rounded-xl border border-gray-100">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Pembeli</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Tanggal Kunjungan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Paket Wisata</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Jumlah & Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Metode Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($reservationsList as $res)
                        <tr class="hover:bg-gray-50/30">
                            <td class="px-4 py-3.5 font-semibold text-charcoal">
                                {{ $res['guest_name'] }}
                                @if ($res['is_walkin'])
                                    <span class="ml-1 inline-flex items-center rounded bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">Walk-in</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-gray-600">{{ \Carbon\Carbon::parse($res['scheduled_date'])->translatedFormat('d M Y') }}</td>
                            <td class="px-4 py-3.5 text-gray-600">{{ $res['package_name'] }}</td>
                            <td class="px-4 py-3.5 text-gray-600">
                                {{ $res['party_size'] }} Orang<br>
                                <span class="text-xs font-semibold text-charcoal">Rp {{ number_format($res['total_amount'], 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-500 font-semibold uppercase text-xs">
                                <span class="rounded bg-gray-100 px-1.5 py-0.5">{{ $res['payment_method'] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">Tidak ada data transaksi untuk rentang ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
