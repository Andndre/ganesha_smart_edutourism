@extends('layouts.dashboard')

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
        <div class="flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Wisatawan Saat Ini</p>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-5xl font-bold text-charcoal">{{ $totalCurrentCount }}</span>
                <span class="text-lg text-gray-400">/ {{ $totalMaxCapacity }} kapasitas total</span>
            </div>
            @php
                $overallPct = $totalMaxCapacity > 0 ? round(($totalCurrentCount / $totalMaxCapacity) * 100, 1) : 0;
                if ($overallPct >= 80) {
                    $overallStatus = 'Kapasitas Penuh';
                    $overallColor = 'text-warning';
                    $overallBarColor = 'bg-warning';
                } elseif ($overallPct >= 60) {
                    $overallStatus = 'Kapasitas Sedang';
                    $overallColor = 'text-secondary';
                    $overallBarColor = 'bg-secondary';
                } else {
                    $overallStatus = 'Kapasitas Aman';
                    $overallColor = 'text-primary';
                    $overallBarColor = 'bg-primary';
                }
            @endphp
            <div class="mt-3 h-3 overflow-hidden rounded-full bg-gray-100">
                <div class="h-full {{ $overallBarColor }} transition-all" style="width: {{ min(100, $overallPct) }}%"></div>
            </div>
            <p class="mt-1.5 text-sm text-gray-500">{{ $overallPct }}% — <span class="font-semibold {{ $overallColor }}">{{ $overallStatus }}</span></p>
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
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    @foreach ($zones as $zone)
        @php
            $pct = $zone->max_capacity > 0 ? round(($zone->current_count / $zone->max_capacity) * 100) : 0;
            if ($pct >= ($zone->critical_threshold ?? 80)) {
                $statusLabel = 'Kritis';
                $barColor    = 'bg-warning';
                $badgeClass  = 'bg-warning/10 text-warning';
                $borderClass = 'border-warning/20';
            } elseif ($pct >= ($zone->warning_threshold ?? 60)) {
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
                <div>
                    <h3 class="font-semibold text-charcoal">{{ $zone->name }}</h3>
                    <span class="text-[10px] text-gray-400">Limit: {{ $zone->warning_threshold }}% Warning / {{ $zone->critical_threshold }}% Critical</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badgeClass }}">{{ $statusLabel }}</span>
                    @if($zone->id)
                        <button onclick="openThresholdModal({{ json_encode([
                            'id' => $zone->id,
                            'name' => $zone->name,
                            'warning_threshold' => $zone->warning_threshold,
                            'critical_threshold' => $zone->critical_threshold,
                            'max_capacity' => $zone->max_capacity
                        ]) }})" class="text-gray-400 hover:text-primary transition-colors focus:outline-none" title="Edit Ambang Batas">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
            <div class="flex items-baseline gap-1">
                <span class="text-3xl font-bold text-charcoal">{{ $zone->current_count }}</span>
                <span class="text-gray-400 font-medium">/ {{ $zone->max_capacity }} orang</span>
            </div>
            <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100">
                <div class="{{ $barColor }} h-full rounded-full transition-all" style="width: {{ min(100, $pct) }}%"></div>
            </div>
            <div class="mt-2.5 flex items-center justify-between text-xs text-gray-500">
                <span>{{ $pct }}% terisi</span>
                <span class="font-medium">Identitas: {{ $zone->zone_identifier }}</span>
            </div>
        </div>
    @endforeach
</div>

{{-- Real-time Map --}}
<div class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm relative">
    <h3 class="mb-4 font-semibold text-charcoal">Pemantauan Lokasi Real-time</h3>
    <div class="relative w-full h-[400px] rounded-xl overflow-hidden border border-gray-200">
        <div id="map" class="h-full w-full z-10 relative"></div>
        <div id="heatmap-overlay" class="absolute inset-0 pointer-events-none z-[1000] overflow-hidden"></div>
    </div>
</div>

{{-- Historical 24h chart --}}
<div class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="mb-4 font-semibold text-charcoal">Tren Kunjungan 24 Jam Terakhir</h3>
    <canvas id="capacityChart" class="w-full" height="160"></canvas>
</div>

{{-- Edit Threshold Modal --}}
<div id="threshold-modal" class="fixed inset-0 z-50 hidden overflow-y-auto p-4 bg-black/50 backdrop-blur-sm justify-center">
    <div class="w-full max-w-md my-auto self-start overflow-hidden rounded-2xl bg-white shadow-xl animate-fade-in">
        <div class="border-b border-gray-100 px-6 py-4 flex items-center justify-between">
            <h3 class="font-display text-lg font-bold text-charcoal">Edit Ambang Batas <span id="modal-zone-name" class="text-gray-400"></span></h3>
            <button onclick="closeThresholdModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form id="modal-threshold-form" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label for="modal-max-capacity" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Kapasitas Maksimal (Orang)</label>
                    <input type="number" name="max_capacity" id="modal-max-capacity" required min="1" class="w-full rounded-xl border border-gray-200 px-3.5 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary bg-white text-charcoal">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="modal-warning-threshold" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Warning (%)</label>
                        <input type="number" name="warning_threshold" id="modal-warning-threshold" required min="1" max="100" class="w-full rounded-xl border border-gray-200 px-3.5 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary bg-white text-charcoal">
                    </div>
                    <div>
                        <label for="modal-critical-threshold" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Critical (%)</label>
                        <input type="number" name="critical_threshold" id="modal-critical-threshold" required min="1" max="100" class="w-full rounded-xl border border-gray-200 px-3.5 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary bg-white text-charcoal">
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-100 px-6 py-4 flex justify-end gap-2 bg-gray-50/50">
                <button type="button" onclick="closeThresholdModal()" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-500 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" class="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white shadow-md shadow-primary/20 hover:bg-primary-dark">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .leaflet-control-attribution {
        display: none !important;
    }
    .heatmap-cell {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(239,68,68,0.8) 0%, rgba(249,115,22,0.6) 40%, rgba(250,204,21,0.3) 70%, transparent 100%);
        mix-blend-mode: multiply;
        transition: all 0.5s ease;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    function openThresholdModal(data) {
        document.getElementById('modal-zone-name').innerText = data.name;
        document.getElementById('modal-max-capacity').value = data.max_capacity;
        document.getElementById('modal-warning-threshold').value = data.warning_threshold;
        document.getElementById('modal-critical-threshold').value = data.critical_threshold;

        const form = document.getElementById('modal-threshold-form');
        form.action = `/admin/capacity/${data.id}/thresholds`;

        const modal = document.getElementById('threshold-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeThresholdModal() {
        const modal = document.getElementById('threshold-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

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

        const data = {{ json_encode($hourlyData) }};
        const max = Math.max(...data, 100) * 1.15;
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

    // Initialize Leaflet Map
    const defaultLat = {{ $defaultLat }};
    const defaultLon = {{ $defaultLon }};
    
    const map = L.map('map', {
        zoomControl: true
    }).setView([defaultLat, defaultLon], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    let heatmapData = @json($heatmapData);
    const liveUserMarkers = {};

    // Initial markers for live users loaded from server
    heatmapData.forEach(point => {
        if (point.is_live_user) {
            const liveIcon = L.divIcon({
                className: 'custom-div-icon',
                html: `
                    <div class="relative flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-600 border-2 border-white shadow"></span>
                    </div>
                `,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });
            
            const marker = L.marker([point.lat, point.lng], { icon: liveIcon })
                .bindPopup('Wisatawan (Live)')
                .addTo(map);
            
            liveUserMarkers[point.session_id] = marker;
        }
    });

    function renderHeatmap() {
        const overlay = document.getElementById('heatmap-overlay');
        overlay.innerHTML = '';

        const mapBounds = map.getBounds();

        heatmapData.forEach(point => {
            const latLng = L.latLng(point.lat, point.lng);
            if (!mapBounds.contains(latLng)) return;

            const pointPos = map.latLngToContainerPoint(latLng);
            const size = 80 + (point.intensity * 60);

            const cell = document.createElement('div');
            cell.className = 'heatmap-cell';
            cell.style.left = (pointPos.x - size / 2) + 'px';
            cell.style.top = (pointPos.y - size / 2) + 'px';
            cell.style.width = size + 'px';
            cell.style.height = size + 'px';
            cell.style.opacity = point.intensity * 0.6;

            overlay.appendChild(cell);
        });
    }

    // Re-render heatmap when map moves
    map.on('moveend', renderHeatmap);
    map.on('zoomend', renderHeatmap);

    // Initial render
    renderHeatmap();

    // Listen for WebSocket updates
    if (window.Echo) {
        window.Echo.channel('village-map')
            .listen('VisitorLocationUpdated', (e) => {
                const existingIndex = heatmapData.findIndex(p => p.session_id === e.session_id);
                
                const newPoint = {
                    lat: parseFloat(e.latitude),
                    lng: parseFloat(e.longitude),
                    intensity: 0.9,
                    category: 'cultural',
                    name: 'Pengunjung Aktif',
                    is_live_user: true,
                    session_id: e.session_id
                };

                if (existingIndex !== -1) {
                    heatmapData[existingIndex] = newPoint;
                } else {
                    heatmapData.push(newPoint);
                }

                renderHeatmap();

                // Create or update marker for the live user
                if (liveUserMarkers[e.session_id]) {
                    liveUserMarkers[e.session_id].setLatLng([e.latitude, e.longitude]);
                } else {
                    const liveIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `
                            <div class="relative flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-blue-600 border-2 border-white shadow"></span>
                            </div>
                        `,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8]
                    });
                    
                    const marker = L.marker([e.latitude, e.longitude], { icon: liveIcon })
                        .bindPopup('Wisatawan (Live)')
                        .addTo(map);
                    
                    liveUserMarkers[e.session_id] = marker;
                }
            });
    }
</script>
@endpush
