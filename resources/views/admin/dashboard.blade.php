@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')

{{-- Page Header --}}
<div class="mb-8 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Dashboard</h1>
        <p class="mt-0.5 text-sm text-gray-500" id="live-datetime"></p>
    </div>
    <a href="{{ route('admin.reports') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Unduh Laporan
    </a>
</div>

{{-- ============================================================
     TOURIST CAPACITY WARNING SYSTEM
     ============================================================ --}}
<div class="mb-8 overflow-hidden rounded-2xl border border-warning/20 bg-warning/5 p-5">
    <div class="flex items-start gap-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-warning/10">
            <svg class="h-5 w-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div class="flex-1">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="font-semibold text-warning">Peringatan Kapasitas Wisatawan</h2>
                <span class="rounded-full bg-warning/15 px-2.5 py-0.5 text-xs font-bold text-warning">SEDANG</span>
            </div>
            <p class="mt-1 text-sm text-gray-600">Zona Utama mendekati batas kapasitas. Pertimbangkan untuk mengalihkan wisatawan ke jalur alternatif.</p>
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @php
                    $zones = [
                        ['name' => 'Zona Utama',     'current' => 312, 'max' => 400, 'color' => 'warning'],
                        ['name' => 'Area UMKM',      'current' => 178, 'max' => 300, 'color' => 'success'],
                        ['name' => 'Pura Penataran', 'current' => 85,  'max' => 150, 'color' => 'success'],
                        ['name' => 'Kebun Bambu',    'current' => 42,  'max' => 200, 'color' => 'success'],
                    ];
                @endphp
                @foreach ($zones as $zone)
                    @php
                        $pct = round(($zone['current'] / $zone['max']) * 100);
                        $barColor = $pct >= 80 ? 'bg-warning' : ($pct >= 60 ? 'bg-secondary' : 'bg-primary');
                        $textColor = $pct >= 80 ? 'text-warning' : 'text-primary';
                    @endphp
                    <div class="rounded-xl bg-white p-3 shadow-sm">
                        <p class="text-[11px] font-semibold text-gray-500">{{ $zone['name'] }}</p>
                        <p class="mt-0.5 text-lg font-bold {{ $textColor }}">{{ $zone['current'] }}<span class="text-xs font-normal text-gray-400">/{{ $zone['max'] }}</span></p>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="{{ $barColor }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="mt-1 text-[10px] text-gray-400">{{ $pct }}% kapasitas</p>
                    </div>
                @endforeach
            </div>
        </div>
        <a href="{{ route('admin.capacity') }}" class="shrink-0 text-xs font-semibold text-warning underline underline-offset-2 hover:no-underline">
            Detail →
        </a>
    </div>
</div>

{{-- ============================================================
     KPI STAT CARDS
     ============================================================ --}}
<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @php
        $stats = [
            ['label' => 'Pengunjung Hari Ini',  'value' => '617',   'unit' => 'orang',  'delta' => '+12%', 'up' => true,  'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H3v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'primary'],
            ['label' => 'Pendapatan Hari Ini',  'value' => '4.2',   'unit' => 'Juta',   'delta' => '+8%',  'up' => true,  'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'secondary'],
            ['label' => 'Tiket Aktif',          'value' => '89',    'unit' => 'tiket',  'delta' => '-3%',  'up' => false, 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', 'color' => 'primary'],
            ['label' => 'Rating Kepuasan',      'value' => '4.7',   'unit' => '/ 5.0',  'delta' => '+0.2', 'up' => true,  'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'secondary'],
        ];
    @endphp
    @foreach ($stats as $i => $stat)
        <div class="stat-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" style="animation-delay: {{ $i * 80 }}ms">
            <div class="flex items-start justify-between">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $stat['color'] }}/10">
                    <svg class="h-5 w-5 text-{{ $stat['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                    </svg>
                </div>
                <span class="text-xs font-semibold {{ $stat['up'] ? 'text-primary' : 'text-warning' }}">
                    {{ $stat['delta'] }} {{ $stat['up'] ? '↑' : '↓' }}
                </span>
            </div>
            <p class="mt-4 text-2xl font-bold text-charcoal">
                {{ $stat['value'] }}<span class="ml-1 text-sm font-normal text-gray-400">{{ $stat['unit'] }}</span>
            </p>
            <p class="mt-0.5 text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
        </div>
    @endforeach
</div>

{{-- ============================================================
     VISITOR TREND CHART + QUICK ACTIONS
     ============================================================ --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

    {{-- Chart (takes 2/3 width on desktop) --}}
    <div class="lg:col-span-2">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm h-full flex flex-col"> {{-- Tambahkan h-full dan flex flex-col --}}
            <div class="mb-5 flex items-center justify-between">
                <h3 class="font-semibold text-charcoal">Tren Pengunjung — 7 Hari Terakhir</h3>
                <span class="rounded-lg bg-primary/8 px-2.5 py-1 text-xs font-semibold text-primary">Mingguan</span>
            </div>
            <div class="grow flex items-center justify-center">
                <canvas id="visitorChart" class="w-full"></canvas>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="flex flex-col gap-4">
        <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
            <h3 class="mb-4 font-semibold text-charcoal">Aksi Cepat</h3>
            <div class="space-y-2">
                @php
                    $quickLinks = [
                        ['label' => 'Tambah Event Baru',       'route' => 'admin.events.create',    'icon' => 'M12 4v16m8-8H4'],
                        ['label' => 'Tambah Paket Wisata',     'route' => 'admin.packages.create',  'icon' => 'M12 4v16m8-8H4'],
                        ['label' => 'Monitor Kapasitas',       'route' => 'admin.capacity',         'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
                        ['label' => 'Lihat Ulasan Terbaru',    'route' => 'admin.feedback',         'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                        ['label' => 'Kelola Pemesanan',        'route' => 'admin.bookings',         'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ];
                @endphp
                @foreach ($quickLinks as $link)
                    <a href="{{ route($link['route']) }}"
                        class="flex items-center gap-3 rounded-xl p-3 text-sm font-medium text-gray-700 transition-all hover:bg-primary/5 hover:text-primary">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-gray-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                            </svg>
                        </div>
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

</div>

{{-- ============================================================
     RECENT BOOKINGS TABLE
     ============================================================ --}}
<div class="mt-6 rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
        <h3 class="font-semibold text-charcoal">Pemesanan Terbaru</h3>
        <a href="{{ route('admin.bookings') }}" class="text-xs font-semibold text-primary hover:underline">Lihat Semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-50 bg-gray-50/50">
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">ID</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Wisatawan</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Paket</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Tanggal</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php
                    $bookings = [
                        ['id' => '#BK-0041', 'name' => 'Sari Dewi',     'pkg' => 'Paket Keluarga 1 Hari', 'date' => '21 Mei 2026', 'status' => 'Aktif'],
                        ['id' => '#BK-0040', 'name' => 'Budi Santoso',  'pkg' => 'Paket Edukasi Budaya',  'date' => '21 Mei 2026', 'status' => 'Selesai'],
                        ['id' => '#BK-0039', 'name' => 'Maria Tan',     'pkg' => 'Paket Sunrise Trek',    'date' => '20 Mei 2026', 'status' => 'Aktif'],
                        ['id' => '#BK-0038', 'name' => 'Reza Pratama',  'pkg' => 'Paket Keluarga 1 Hari', 'date' => '20 Mei 2026', 'status' => 'Dibatalkan'],
                        ['id' => '#BK-0037', 'name' => 'Lisa Cahyani',  'pkg' => 'Paket Edukasi Budaya',  'date' => '19 Mei 2026', 'status' => 'Selesai'],
                    ];
                @endphp
                @foreach ($bookings as $b)
                    @php
                        $badge = match($b['status']) {
                            'Aktif'      => 'bg-primary/10 text-primary',
                            'Selesai'    => 'bg-gray-100 text-gray-500',
                            'Dibatalkan' => 'bg-warning/10 text-warning',
                            default      => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3.5 font-mono text-xs text-gray-400">{{ $b['id'] }}</td>
                        <td class="px-5 py-3.5 font-medium text-charcoal">{{ $b['name'] }}</td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $b['pkg'] }}</td>
                        <td class="px-5 py-3.5 text-gray-500">{{ $b['date'] }}</td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ $b['status'] }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Live datetime
    function updateDatetime() {
        const el = document.getElementById('live-datetime');
        if (!el) return;
        const now = new Date();
        el.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
            + ' • ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    }
    updateDatetime();
    setInterval(updateDatetime, 60000);

    // Simple bar chart using Canvas
    (function () {
        const canvas = document.getElementById('visitorChart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const W = canvas.offsetWidth;
        const H = 180;
        canvas.width  = W * dpr;
        canvas.height = H * dpr;
        canvas.style.height = H + 'px';
        ctx.scale(dpr, dpr);

        const labels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        const data   = [412, 380, 520, 490, 610, 730, 617];
        const max    = Math.max(...data) * 1.15;

        const padL = 36, padR = 12, padT = 12, padB = 32;
        const chartW = W - padL - padR;
        const chartH = H - padT - padB;
        const barW = chartW / (data.length * 1.6);
        const gap  = chartW / data.length;

        // Grid lines
        ctx.strokeStyle = '#f3f4f6';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 4; i++) {
            const y = padT + chartH - (chartH * i / 4);
            ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(W - padR, y); ctx.stroke();
        }

        // Bars
        data.forEach((val, i) => {
            const x = padL + gap * i + gap / 2 - barW / 2;
            const barH = (val / max) * chartH;
            const y = padT + chartH - barH;

            // Gradient fill
            const grad = ctx.createLinearGradient(0, y, 0, padT + chartH);
            grad.addColorStop(0, '#1e5128');
            grad.addColorStop(1, '#1e512840');
            ctx.fillStyle = grad;

            // Rounded top
            const r = Math.min(6, barW / 2);
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.lineTo(x + barW - r, y);
            ctx.quadraticCurveTo(x + barW, y, x + barW, y + r);
            ctx.lineTo(x + barW, padT + chartH);
            ctx.lineTo(x, padT + chartH);
            ctx.lineTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.closePath();
            ctx.fill();

            // Label
            ctx.fillStyle = '#9ca3af';
            ctx.font = '11px Plus Jakarta Sans, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(labels[i], x + barW / 2, H - 8);

            // Value on top of bar
            ctx.fillStyle = '#191a19';
            ctx.font = 'bold 10px Plus Jakarta Sans, sans-serif';
            ctx.fillText(val, x + barW / 2, y - 4);
        });
    })();
</script>
@endpush
