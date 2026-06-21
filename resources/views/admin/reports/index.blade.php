@extends('layouts.dashboard')

@section('title', 'Laporan & Analitik')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Laporan & Analitik</h1>
        <p class="mt-0.5 text-sm text-gray-500">Ringkasan performa desa wisata secara periodik.</p>
    </div>
    <div class="flex items-center gap-2">
        <select id="period-selector" onchange="window.location.href = '{{ route('admin.reports') }}?period=' + this.value" class="rounded-xl border border-gray-200 px-3 py-2.5 text-sm focus:border-primary focus:outline-none bg-white text-charcoal">
            <option value="Mei 2026" {{ $selectedPeriod === 'Mei 2026' ? 'selected' : '' }}>Mei 2026</option>
            <option value="April 2026" {{ $selectedPeriod === 'April 2026' ? 'selected' : '' }}>April 2026</option>
            <option value="Maret 2026" {{ $selectedPeriod === 'Maret 2026' ? 'selected' : '' }}>Maret 2026</option>
        </select>
        <a href="{{ route('admin.reports.download', ['period' => $selectedPeriod]) }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-dark">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Unduh PDF
        </a>
    </div>
</div>

{{-- Monthly KPI Summary --}}
<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    {{-- Visitor KPI --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Pengunjung</p>
        <p class="mt-2 text-2xl font-bold text-charcoal">{{ number_format($visitorCount, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs font-semibold {{ $visitorDelta >= 0 ? 'text-primary' : 'text-warning' }}">
            {{ ($visitorDelta >= 0 ? '+' : '') . $visitorDelta }}% {{ $visitorDelta >= 0 ? '↑' : '↓' }} vs. bulan lalu
        </p>
    </div>
    {{-- Revenue KPI --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Pendapatan</p>
        <p class="mt-2 text-2xl font-bold text-charcoal">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs font-semibold {{ $revenueDelta >= 0 ? 'text-primary' : 'text-warning' }}">
            {{ ($revenueDelta >= 0 ? '+' : '') . $revenueDelta }}% {{ $revenueDelta >= 0 ? '↑' : '↓' }} vs. bulan lalu
        </p>
    </div>
    {{-- Tickets KPI --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Tiket Terjual</p>
        <p class="mt-2 text-2xl font-bold text-charcoal">{{ number_format($ticketsSold, 0, ',', '.') }}</p>
        <p class="mt-1 text-xs font-semibold {{ $ticketsDelta >= 0 ? 'text-primary' : 'text-warning' }}">
            {{ ($ticketsDelta >= 0 ? '+' : '') . $ticketsDelta }}% {{ $ticketsDelta >= 0 ? '↑' : '↓' }} vs. bulan lalu
        </p>
    </div>
    {{-- Rating KPI --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Rating Kepuasan</p>
        <p class="mt-2 text-2xl font-bold text-charcoal">{{ $rating }} ★</p>
        <p class="mt-1 text-xs font-semibold {{ $ratingDelta >= 0 ? 'text-primary' : 'text-warning' }}">
            {{ ($ratingDelta >= 0 ? '+' : '') . $ratingDelta }} {{ $ratingDelta >= 0 ? '↑' : '↓' }} vs. bulan lalu
        </p>
    </div>
</div>

{{-- Charts Row --}}
<div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-semibold text-charcoal">Pengunjung per Hari ({{ $selectedPeriod }})</h3>
        <canvas id="monthlyChart" class="w-full" height="180"></canvas>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-semibold text-charcoal">Pendapatan per Kategori Paket</h3>
        <div class="space-y-3">
            @foreach ($revenueBreakdown as $r)
                <div>
                    <div class="mb-1 flex items-center justify-between text-sm">
                        <span class="font-medium text-charcoal">{{ $r['label'] }}</span>
                        <span class="font-bold text-primary">{{ $r['amount'] }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-primary" style="width: {{ $r['pct'] }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Top Pages / Origin --}}
<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-semibold text-charcoal">Asal Daerah Wisatawan</h3>
        <div class="space-y-2">
            @php
                $origins = [
                    ['city' => 'Denpasar', 'pct' => 28],
                    ['city' => 'Jakarta',  'pct' => 22],
                    ['city' => 'Surabaya', 'pct' => 16],
                    ['city' => 'Bandung',  'pct' => 12],
                    ['city' => 'Lainnya',  'pct' => 22],
                ];
            @endphp
            @foreach ($origins as $o)
                <div class="flex items-center gap-3">
                    <span class="w-20 text-sm text-gray-600">{{ $o['city'] }}</span>
                    <div class="flex-1 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-secondary" style="width: {{ $o['pct'] }}%"></div>
                    </div>
                    <span class="w-8 text-right text-xs font-bold text-gray-500">{{ $o['pct'] }}%</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-4 font-semibold text-charcoal">Hari Tersibuk Bulan Ini</h3>
        <div class="space-y-2">
            @foreach ($busyDays as $d)
                <div class="flex items-center gap-3">
                    <span class="w-16 text-sm text-gray-600">{{ $d['day'] }}</span>
                    <div class="flex-1 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div class="h-full rounded-full bg-primary" style="width: {{ $d['pct'] }}%"></div>
                    </div>
                    <span class="w-12 text-right text-xs font-bold text-gray-500">{{ $d['visitors'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const canvas = document.getElementById('monthlyChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const W = canvas.offsetWidth;
        const H = 180;
        canvas.width  = W * dpr;
        canvas.height = H * dpr;
        canvas.style.height = H + 'px';
        ctx.scale(dpr, dpr);

        const data = {{ json_encode($chartData) }};
        const max = Math.max(...data) * 1.15;
        const padL = 36, padR = 12, padT = 12, padB = 28;
        const chartW = W - padL - padR;
        const chartH = H - padT - padB;

        // Area fill
        ctx.beginPath();
        data.forEach((v, i) => {
            const x = padL + (i / (data.length - 1)) * chartW;
            const y = padT + chartH - (v / max) * chartH;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.lineTo(padL + chartW, padT + chartH);
        ctx.lineTo(padL, padT + chartH);
        ctx.closePath();
        const grad = ctx.createLinearGradient(0, padT, 0, padT + chartH);
        grad.addColorStop(0, '#1e512830');
        grad.addColorStop(1, '#1e512800');
        ctx.fillStyle = grad;
        ctx.fill();

        // Line
        ctx.beginPath();
        data.forEach((v, i) => {
            const x = padL + (i / (data.length - 1)) * chartW;
            const y = padT + chartH - (v / max) * chartH;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.strokeStyle = '#1e5128';
        ctx.lineWidth = 2;
        ctx.stroke();

        // X labels (every 7 days)
        ctx.fillStyle = '#9ca3af';
        ctx.font = '10px Plus Jakarta Sans, sans-serif';
        ctx.textAlign = 'center';
        const selectedPeriod = "{{ $selectedPeriod }}";
        const labelSuffix = selectedPeriod.split(' ')[0] || 'Mei';
        
        [1, 7, 14, 21].forEach(d => {
            const x = padL + ((d - 1) / (data.length - 1)) * chartW;
            ctx.fillText(d + ' ' + labelSuffix, x, H - 6);
        });
    })();
</script>
@endpush
