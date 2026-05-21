@extends('layouts.admin')

@section('title', 'Kapasitas Wisatawan')

@section('content')

<div class="mb-8 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Sistem Peringatan Kapasitas</h1>
        <p class="mt-0.5 text-sm text-gray-500">Pemantauan kepadatan wisatawan secara real-time per zona.</p>
    </div>
    <div class="flex items-center gap-2">
        <span class="flex items-center gap-1.5 rounded-full bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
            </span>
            Live — Diperbarui tiap menit
        </span>
    </div>
</div>

{{-- Overall Crowd Level --}}
<div class="mb-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Wisatawan Saat Ini</p>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-5xl font-bold text-charcoal">617</span>
                <span class="text-lg text-gray-400">/ 1.050 kapasitas total</span>
            </div>
            <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full rounded-full bg-warning transition-all" style="width: 58.8%"></div>
            </div>
            <p class="mt-1.5 text-sm text-gray-500">58.8% — <span class="font-semibold text-warning">Kapasitas Sedang</span></p>
        </div>
        <div class="grid grid-cols-3 gap-3 sm:text-right">
            @php
                $levels = [
                    ['label' => 'Aman',   'range' => '< 60%',  'color' => 'bg-primary/10 text-primary'],
                    ['label' => 'Sedang', 'range' => '60–80%', 'color' => 'bg-secondary/15 text-secondary'],
                    ['label' => 'Penuh',  'range' => '> 80%',  'color' => 'bg-warning/10 text-warning'],
                ];
            @endphp
            @foreach ($levels as $l)
                <div class="rounded-xl p-3 text-center {{ $l['color'] }}">
                    <p class="text-xs font-bold">{{ $l['label'] }}</p>
                    <p class="text-[11px] opacity-70">{{ $l['range'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Zone Cards --}}
@php
    $zones = [
        ['name' => 'Zona Utama (Jalan Utama)',    'current' => 312, 'max' => 400, 'trend' => '+18 / jam',  'note' => 'Mendekati batas — pertimbangkan pengalihan rute.'],
        ['name' => 'Area UMKM & Pasar',           'current' => 178, 'max' => 300, 'trend' => '+5 / jam',   'note' => 'Normal.'],
        ['name' => 'Pura Penataran Agung',        'current' => 85,  'max' => 150, 'trend' => '-2 / jam',   'note' => 'Normal.'],
        ['name' => 'Kebun Bambu & Jalur Trekking','current' => 42,  'max' => 200, 'trend' => '+1 / jam',   'note' => 'Masih sangat longgar.'],
    ];
@endphp
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    @foreach ($zones as $zone)
        @php
            $pct = round(($zone['current'] / $zone['max']) * 100);
            if ($pct >= 80) {
                $statusLabel = 'Kritis';
                $barColor    = 'bg-warning';
                $badgeClass  = 'bg-warning/10 text-warning';
                $borderClass = 'border-warning/20';
            } elseif ($pct >= 60) {
                $statusLabel = 'Sedang';
                $barColor    = 'bg-secondary';
                $badgeClass  = 'bg-secondary/15 text-secondary-700';
                $borderClass = 'border-secondary/20';
            } else {
                $statusLabel = 'Aman';
                $barColor    = 'bg-primary';
                $badgeClass  = 'bg-primary/10 text-primary';
                $borderClass = 'border-gray-100';
            }
        @endphp
        <div class="rounded-2xl border {{ $borderClass }} bg-white p-5 shadow-sm">
            <div class="mb-3 flex items-start justify-between gap-2">
                <h3 class="font-semibold text-charcoal">{{ $zone['name'] }}</h3>
                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badgeClass }}">{{ $statusLabel }}</span>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-bold text-charcoal">{{ $zone['current'] }}</span>
                <span class="text-gray-400">/ {{ $zone['max'] }} orang</span>
            </div>
            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100">
                <div class="{{ $barColor }} h-full rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-xs text-gray-500">
                <span>{{ $pct }}% terisi</span>
                <span class="font-medium">Tren: {{ $zone['trend'] }}</span>
            </div>
            <p class="mt-2 text-xs text-gray-400">{{ $zone['note'] }}</p>
        </div>
    @endforeach
</div>

{{-- Historical 24h chart placeholder --}}
<div class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="mb-4 font-semibold text-charcoal">Tren Kunjungan 24 Jam Terakhir</h3>
    <canvas id="capacityChart" class="w-full" height="160"></canvas>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const canvas = document.getElementById('capacityChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const W = canvas.offsetWidth;
        const H = 160;
        canvas.width  = W * dpr;
        canvas.height = H * dpr;
        canvas.style.height = H + 'px';
        ctx.scale(dpr, dpr);

        // Mock hourly data (0–23h)
        const hours = Array.from({length: 24}, (_, i) => i);
        const data  = [12,8,5,3,2,8,45,120,280,390,480,530,580,617,590,540,480,410,330,260,180,120,80,40];
        const max   = 700;
        const padL  = 36, padR = 12, padT = 12, padB = 28;
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
        grad.addColorStop(0, '#1e512825');
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

        // Hour labels (every 4h)
        ctx.fillStyle = '#9ca3af';
        ctx.font = '10px Plus Jakarta Sans, sans-serif';
        ctx.textAlign = 'center';
        [0,4,8,12,16,20,23].forEach(h => {
            const x = padL + (h / 23) * chartW;
            ctx.fillText(h + ':00', x, H - 6);
        });
    })();
</script>
@endpush
