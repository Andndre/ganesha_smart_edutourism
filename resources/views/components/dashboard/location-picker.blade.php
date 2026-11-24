@props(['id', 'latitude' => null, 'longitude' => null, 'label' => 'Titik lokasi', 'latitudeName' => 'latitude', 'longitudeName' => 'longitude', 'hideCoordinates' => false, 'confirmSelection' => false])

<div data-location-picker data-picker-id="{{ $id }}"
    data-hide-coordinates="{{ $hideCoordinates ? 'true' : 'false' }}" data-confirm-selection="{{ $confirmSelection ? 'true' : 'false' }}"
    data-center-lat="{{ config('services.penglipuran.latitude') }}"
    data-center-lng="{{ config('services.penglipuran.longitude') }}"
    data-center-zoom="{{ config('services.penglipuran.zoom') }}">
    <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-charcoal">{{ $label }}</h3>
            <p class="mt-1 text-xs leading-relaxed text-gray-500">Ketuk peta untuk menempatkan pin. Geser pin untuk menyesuaikan posisi.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-zoom-in aria-label="Perbesar peta" class="min-h-11 min-w-11 rounded-xl border border-gray-200 bg-white text-lg font-bold text-charcoal transition hover:bg-surface">+</button>
            <button type="button" data-zoom-out aria-label="Perkecil peta" class="min-h-11 min-w-11 rounded-xl border border-gray-200 bg-white text-lg font-bold text-charcoal transition hover:bg-surface">−</button>
            <button type="button" data-select-center class="min-h-11 rounded-xl bg-primary px-3 text-xs font-bold text-white transition hover:bg-primary-hover">Pilih pusat peta</button>
            <button type="button" data-clear-location class="min-h-11 rounded-xl border border-gray-200 px-3 text-xs font-bold text-gray-600 transition hover:bg-surface">Hapus titik</button>
        </div>
    </div>
    <div id="{{ $id }}-map" data-location-map class="h-72 w-full overflow-hidden rounded-2xl border border-gray-200 bg-surface sm:h-96" role="application" aria-label="Peta pemilih lokasi"></div>
    @if($confirmSelection)
        <button type="button" data-confirm-point class="mt-3 inline-flex min-h-12 items-center justify-center rounded-xl bg-primary px-5 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">Gunakan titik ini</button>
    @endif
    <div class="mt-3 flex items-center gap-2 rounded-xl bg-primary/5 px-3 py-2.5 text-xs font-semibold text-primary" aria-live="polite">
        <span class="h-2 w-2 shrink-0 rounded-full bg-primary"></span>
        <span data-location-status>Belum ada titik dipilih</span>
    </div>
    <input type="hidden" name="{{ $latitudeName }}" value="{{ $latitude }}" data-location-lat>
    <input type="hidden" name="{{ $longitudeName }}" value="{{ $longitude }}" data-location-lng>
    @error($latitudeName)<p class="mt-2 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
    @error($longitudeName)<p class="mt-2 text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
</div>

@pushOnce('styles', 'dashboard-location-picker-style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endPushOnce

@pushOnce('scripts', 'dashboard-location-picker-script')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        function initializeDashboardLocationPickers() {
            if (typeof L === 'undefined') return;

            document.querySelectorAll('[data-location-picker]:not([data-map-ready])').forEach(picker => {
                picker.dataset.mapReady = 'true';
                const latInput = picker.querySelector('[data-location-lat]');
                const lngInput = picker.querySelector('[data-location-lng]');
                const status = picker.querySelector('[data-location-status]');
                const savedLat = Number.parseFloat(latInput.value);
                const savedLng = Number.parseFloat(lngInput.value);
                const hasSavedPoint = Number.isFinite(savedLat) && Number.isFinite(savedLng);
                const center = hasSavedPoint
                    ? [savedLat, savedLng]
                    : [Number(picker.dataset.centerLat), Number(picker.dataset.centerLng)];
                const map = L.map(picker.querySelector('[data-location-map]'), { scrollWheelZoom: true, zoomControl: false })
                    .setView(center, hasSavedPoint ? 18 : Number(picker.dataset.centerZoom));

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const icon = L.divIcon({
                    className: '',
                    html: '<span class="block h-6 w-6 rounded-full border-[5px] border-white bg-primary shadow-lg ring-2 ring-primary"></span>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12]
                });
                let marker = null;
                const requiresConfirmation = picker.dataset.confirmSelection === 'true';

                const setPoint = (point, confirmed = false) => {
                    if (!marker) {
                        marker = L.marker(point, { draggable: true, icon }).addTo(map);
                        marker.on('dragend', () => setPoint(marker.getLatLng()));
                    } else {
                        marker.setLatLng(point);
                    }
                    latInput.value = point.lat.toFixed(7);
                    lngInput.value = point.lng.toFixed(7);
                    picker.dataset.locationConfirmed = (!requiresConfirmation || confirmed) ? 'true' : 'false';
                    status.textContent = picker.dataset.locationConfirmed === 'true'
                        ? (picker.dataset.hideCoordinates === 'true' ? 'Titik rumah sudah dipilih' : `Titik dipilih · ${latInput.value}, ${lngInput.value}`)
                        : 'Titik dipilih. Tekan Gunakan titik ini untuk memastikan lokasi.';
                    picker.dispatchEvent(new CustomEvent('location-change', { bubbles: true }));
                };

                map.on('click', event => setPoint(event.latlng));
                picker.querySelector('[data-zoom-in]').addEventListener('click', () => map.zoomIn());
                picker.querySelector('[data-zoom-out]').addEventListener('click', () => map.zoomOut());
                picker.querySelector('[data-select-center]').addEventListener('click', () => setPoint(map.getCenter()));
                picker.querySelector('[data-confirm-point]')?.addEventListener('click', () => {
                    if (marker) setPoint(marker.getLatLng(), true);
                    else status.textContent = 'Ketuk lokasi rumah di peta terlebih dahulu.';
                });
                picker.querySelector('[data-clear-location]').addEventListener('click', () => {
                    marker?.remove();
                    marker = null;
                    latInput.value = '';
                    lngInput.value = '';
                    status.textContent = 'Belum ada titik dipilih';
                    picker.dataset.locationConfirmed = 'false';
                    picker.dispatchEvent(new CustomEvent('location-change', { bubbles: true }));
                });
                if (hasSavedPoint) setPoint(L.latLng(savedLat, savedLng), true);
                picker.addEventListener('location-picker:shown', () => requestAnimationFrame(() => map.invalidateSize()));
                requestAnimationFrame(() => map.invalidateSize());
            });
        }

        document.addEventListener('DOMContentLoaded', initializeDashboardLocationPickers);
        document.addEventListener('livewire:navigated', initializeDashboardLocationPickers);
        if (document.readyState !== 'loading') initializeDashboardLocationPickers();
    </script>
@endPushOnce
