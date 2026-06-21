@extends('layouts.dashboard')

@section('title', 'Kapasitas Wisatawan')

@section('content')

    <div class="mb-8 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">Sistem Peringatan Kapasitas</h1>
            <p class="mt-0.5 text-sm text-gray-500">Pemantauan kepadatan wisatawan secara real-time per zona.</p>
        </div>
        <div class="flex items-center gap-2">
            <span
                class="bg-primary/10 text-primary flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold">
                <span class="relative flex h-2 w-2">
                    <span class="bg-primary absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"></span>
                    <span class="bg-primary relative inline-flex h-2 w-2 rounded-full"></span>
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
                    <span class="text-charcoal text-5xl font-bold">{{ $totalCurrentCount }}</span>
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
                    <div class="{{ $overallBarColor }} h-full transition-all" style="width: {{ min(100, $overallPct) }}%">
                    </div>
                </div>
                <p class="mt-1.5 text-sm text-gray-500">{{ $overallPct }}% — <span
                        class="{{ $overallColor }} font-semibold">{{ $overallStatus }}</span></p>
            </div>
            <div class="grid grid-cols-3 gap-3 sm:text-right">
                @php
                    $levels = [
                        ['label' => 'Aman', 'range' => '< 60%', 'color' => 'bg-primary/10 text-primary'],
                        ['label' => 'Sedang', 'range' => '60-80%', 'color' => 'bg-secondary/15 text-secondary'],
                        ['label' => 'Penuh', 'range' => '> 80%', 'color' => 'bg-warning/10 text-warning'],
                    ];
                @endphp
                @foreach ($levels as $l)
                    <div class="{{ $l['color'] }} rounded-xl p-3 text-center">
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
                    $barColor = 'bg-warning';
                    $badgeClass = 'bg-warning/10 text-warning';
                    $borderClass = 'border-warning/20';
                } elseif ($pct >= ($zone->warning_threshold ?? 60)) {
                    $statusLabel = 'Sedang';
                    $barColor = 'bg-secondary';
                    $badgeClass = 'bg-secondary/15 text-secondary-700';
                    $borderClass = 'border-secondary/20';
                } else {
                    $statusLabel = 'Aman';
                    $barColor = 'bg-primary';
                    $badgeClass = 'bg-primary/10 text-primary';
                    $borderClass = 'border-gray-100';
                }
            @endphp
            <div class="{{ $borderClass }} rounded-2xl border bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-start justify-between gap-2">
                    <div>
                        <h3 class="text-charcoal font-semibold">{{ $zone->name }}</h3>
                        <span class="text-[10px] text-gray-400">Limit: {{ $zone->warning_threshold }}% Warning /
                            {{ $zone->critical_threshold }}% Critical</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="{{ $badgeClass }} shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold">{{ $statusLabel }}</span>
                        @if ($zone->id)
                            <button
                                onclick="openThresholdModal({{ json_encode([
                                    'id' => $zone->id,
                                    'name' => $zone->name,
                                    'warning_threshold' => $zone->warning_threshold,
                                    'critical_threshold' => $zone->critical_threshold,
                                    'max_capacity' => $zone->max_capacity,
                                    'radius_meters' => $zone->radius_meters ?? 0,
                                ]) }})"
                                class="hover:text-primary text-gray-400 transition-colors focus:outline-none"
                                title="Edit Ambang Batas">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="flex items-baseline gap-1">
                    <span class="text-charcoal text-3xl font-bold">{{ $zone->current_count }}</span>
                    <span class="font-medium text-gray-400">/ {{ $zone->max_capacity }} orang</span>
                </div>
                <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100">
                    <div class="{{ $barColor }} h-full rounded-full transition-all"
                        style="width: {{ min(100, $pct) }}%"></div>
                </div>
                <div class="mt-2.5 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $pct }}% terisi</span>
                    <span class="font-medium">Identitas: {{ $zone->zone_identifier }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Real-time Map --}}
    <div class="relative mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="text-charcoal mb-4 font-semibold">Pemantauan Lokasi Real-time</h3>
        <div class="relative h-100 w-full overflow-hidden rounded-xl border border-gray-200">
            <div id="map" class="relative z-10 h-full w-full"></div>
            <div id="heatmap-overlay" class="z-1000 pointer-events-none absolute inset-0 overflow-hidden"></div>
        </div>
    </div>

    {{-- Historical 24h chart --}}
    <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="text-charcoal mb-4 font-semibold">Tren Kunjungan 24 Jam Terakhir</h3>
        <canvas id="capacityChart" class="w-full" height="160"></canvas>
    </div>

    {{-- Edit Threshold Modal --}}
    <x-modal name="threshold-modal" maxWidth="md" desktopLayout="drawer">
        <div class="mb-4">
            <h3 class="font-display text-charcoal text-lg font-bold">Edit Ambang Batas <span id="modal-zone-name"
                    class="text-gray-400"></span></h3>
        </div>
        <form id="modal-threshold-form" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="modal-max-capacity"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Kapasitas
                            Maksimal</label>
                        <input type="number" name="max_capacity" id="modal-max-capacity" required min="1"
                            class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                    </div>
                    <div>
                        <label for="modal-radius-meters"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Radius
                            (Meter)</label>
                        <input type="number" name="radius_meters" id="modal-radius-meters" required min="1"
                            class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="modal-warning-threshold"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Warning (%)</label>
                        <input type="number" name="warning_threshold" id="modal-warning-threshold" required min="1"
                            max="100"
                            class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                    </div>
                    <div>
                        <label for="modal-critical-threshold"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Critical (%)</label>
                        <input type="number" name="critical_threshold" id="modal-critical-threshold" required
                            min="1" max="100"
                            class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4">
                <button type="button" onclick="closeThresholdModal()"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-md">
                    Simpan
                </button>
            </div>
        </form>
    </x-modal>

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
            background: radial-gradient(circle, rgba(239, 68, 68, 0.8) 0%, rgba(249, 115, 22, 0.6) 40%, rgba(250, 204, 21, 0.3) 70%, transparent 100%);
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

            if (document.getElementById('modal-radius-meters')) {
                document.getElementById('modal-radius-meters').value = data.radius_meters;
            }

            const form = document.getElementById('modal-threshold-form');
            form.action = `/admin/capacity/${data.id}/thresholds`;

            window.dispatchEvent(new CustomEvent('open-threshold-modal'));
        }

        function closeThresholdModal() {
            window.dispatchEvent(new CustomEvent('close-threshold-modal'));
        }

        (function() {
            const canvas = document.getElementById('capacityChart');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const dpr = window.devicePixelRatio || 1;
            const W = canvas.offsetWidth;
            const H = 160;
            canvas.width = W * dpr;
            canvas.height = H * dpr;
            canvas.style.height = H + 'px';
            ctx.scale(dpr, dpr);

            const data = {{ json_encode($hourlyData) }};
            const max = Math.max(...data, 100) * 1.15;
            const padL = 36,
                padR = 12,
                padT = 12,
                padB = 28;
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
            const labels = @json($hourlyLabels);
            ctx.fillStyle = '#9ca3af';
            ctx.font = '10px Plus Jakarta Sans, sans-serif';
            ctx.textAlign = 'center';
            labels.forEach((label, i) => {
                if (i % 4 === 0 || i === labels.length - 1) {
                    const x = padL + (i / (labels.length - 1)) * chartW;
                    ctx.fillText(label, x, H - 6);
                }
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

                const marker = L.marker([point.lat, point.lng], {
                        icon: liveIcon
                    })
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
        function setupEchoListener() {
            if (window.Echo) {
                window.Echo.channel('village-map')
                    .listen('.VisitorLocationUpdated', (e) => {
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

                            const marker = L.marker([e.latitude, e.longitude], {
                                    icon: liveIcon
                                })
                                .bindPopup('Wisatawan (Live)')
                                .addTo(map);

                            liveUserMarkers[e.session_id] = marker;
                        }
                    });
            } else {
                setTimeout(setupEchoListener, 500);
            }
        }

        setupEchoListener();
    </script>
@endpush
