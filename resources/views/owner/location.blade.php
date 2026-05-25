@extends('layouts.dashboard')

@section('title', 'Kustomisasi Lokasi Toko')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #location-map {
            height: 450px;
            width: 100%;
            border-radius: 16px;
            z-index: 10;
        }

        .custom-pin-selected {
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
        }

        /* Premium Selected Marker Animation */
        @keyframes pin-breath {

            0%,
            100% {
                transform: scale(1);
                filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.15));
            }

            50% {
                transform: scale(1.15);
                filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.25));
            }
        }

        .marker-selected-glow {
            animation: pin-breath 2s infinite ease-in-out;
            transform-origin: center;
        }
    </style>
@endpush

@section('content')
    <div class="mb-8">
        <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Kustomisasi Lokasi Peta</h1>
        <p class="mt-1 text-sm text-gray-500">Tentukan letak geografis toko Anda di peta desa wisata Penglipuran agar
            wisatawan dapat mencari dan bernavigasi ke toko Anda secara real-time.</p>
    </div>

    @if (!$profile)
        <div class="rounded-2xl border border-warning/20 bg-warning/5 p-6 shadow-sm max-w-3xl">
            <div class="flex items-start gap-4">
                <div class="rounded-xl bg-warning/10 p-3 text-warning">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-display text-lg font-bold text-warning-800">Profil Toko Belum Dibuat</h3>
                    <p class="mt-1 text-sm text-warning-700">Silakan isi profil toko Anda terlebih dahulu sebelum
                        mengkustomisasi lokasi toko di peta.</p>
                    <div class="mt-4">
                        <a href="{{ route('owner.profile') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-warning px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-warning/20 transition-all hover:bg-warning-600 active:scale-[0.98]">
                            Buat Profil Toko
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="grid gap-8 lg:grid-cols-3 max-w-6xl">
            {{-- Map Container Card --}}
            <div class="lg:col-span-2 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between px-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Peta Desa Wisata</span>
                    <span class="text-xs font-medium text-primary">Klik di mana saja pada peta atau geser pin untuk memperbarui
                        lokasi</span>
                </div>
                <div id="location-map" class="border border-gray-100 shadow-inner"></div>
            </div>

            {{-- Form Controls Card --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm self-start">
                <h3 class="font-display text-lg font-bold text-charcoal mb-4">Detail Koordinat</h3>

                <form method="POST" action="{{ route('owner.location.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        {{-- Latitude --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Latitude</label>
                            <input type="text" name="latitude" id="field-lat" readonly required
                                value="{{ old('latitude', $location->latitude ?? '') }}"
                                class="mt-1 w-full rounded-xl bg-gray-50 border border-gray-200 px-4 py-2.5 font-mono text-sm text-gray-500 focus:outline-none">
                        </div>

                        {{-- Longitude --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Longitude</label>
                            <input type="text" name="longitude" id="field-lng" readonly required
                                value="{{ old('longitude', $location->longitude ?? '') }}"
                                class="mt-1 w-full rounded-xl bg-gray-50 border border-gray-200 px-4 py-2.5 font-mono text-sm text-gray-500 focus:outline-none">
                        </div>

                        <hr class="border-gray-100">

                        {{-- Accessibility status --}}
                        <div class="flex items-center gap-3 mt-4">
                            <input type="checkbox" name="is_accessible" id="field-accessible" value="1" {{ old('is_accessible', $location->is_accessible ?? true) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
                            <div>
                                <label for="field-accessible" class="text-sm font-semibold text-gray-700">Akses Ramah
                                    Disabilitas</label>
                                <p class="text-xs text-gray-400">Centang jika toko Anda memiliki akses ramah pengguna kursi roda
                                    atau lansia.</p>
                            </div>
                        </div>

                        {{-- Accessibility Notes --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mt-2">Catatan Aksesibilitas</label>
                            <textarea name="accessibility_notes" rows="3"
                                placeholder="Contoh: Tersedia ramp landai di pintu masuk utama."
                                class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">{{ old('accessibility_notes', $location->accessibility_notes ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <button type="submit"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Lokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    @if ($profile)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            const PENGLIPURAN_LAT = -8.421750367447837;
            const PENGLIPURAN_LNG = 115.35900208148409;
            const PENGLIPURAN_ZOOM = 17;

            const savedLat = {{ $location->latitude ?? 'null' }};
            const savedLng = {{ $location->longitude ?? 'null' }};

            let map = null;
            let pinMarker = null;

            document.addEventListener('DOMContentLoaded', function () {
                initMap();
            });

            function initMap() {
                const initialLat = savedLat || PENGLIPURAN_LAT;
                const initialLng = savedLng || PENGLIPURAN_LNG;
                const initialZoom = savedLat ? 18 : PENGLIPURAN_ZOOM;

                map = L.map('location-map', { zoomControl: true, attributionControl: false })
                    .setView([initialLat, initialLng], initialZoom);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19
                }).addTo(map);

                // Marker color violet for UMKM
                const color = '#8B5CF6';
                const pinIcon = L.divIcon({
                    className: 'custom-pin-selected',
                    html: `
                                <div class="relative flex items-center justify-center marker-selected-glow" style="width: 32px; height: 32px;">
                                    <span class="absolute inline-flex h-6 w-6 animate-ping rounded-full opacity-40" style="background-color: ${color};"></span>
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-lg text-white" style="background-color: ${color}; z-index: 10;">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                    </div>
                                </div>
                            `,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });

                // Initialize Marker
                pinMarker = L.marker([initialLat, initialLng], {
                    icon: pinIcon,
                    draggable: true
                }).addTo(map);

                // Pre-fill inputs if no location saved
                if (!savedLat || !savedLng) {
                    updateInputs(initialLat, initialLng);
                }

                // Draggable pin handlers
                pinMarker.on('dragend', function (e) {
                    const pos = pinMarker.getLatLng();
                    updateInputs(pos.lat, pos.lng);
                });

                // Map Click handler
                map.on('click', function (e) {
                    pinMarker.setLatLng(e.latlng);
                    updateInputs(e.latlng.lat, e.latlng.lng);
                });
            }

            function updateInputs(lat, lng) {
                document.getElementById('field-lat').value = parseFloat(lat).toFixed(8);
                document.getElementById('field-lng').value = parseFloat(lng).toFixed(8);
            }
        </script>
    @endif
@endpush