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
    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-3xl font-extrabold text-charcoal tracking-tight">Kustomisasi Lokasi Peta</h1>
            <p class="mt-1 text-sm text-gray-500">Tentukan letak geografis toko Anda di peta desa wisata Penglipuran agar
                wisatawan dapat mencari dan bernavigasi ke toko Anda secara real-time.</p>
        </div>
        @if ($profile)
            <button id="tour-trigger-btn" onclick="startTutorial()"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:bg-gray-100 active:scale-[0.98]"
                title="Panduan Interaktif">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        @endif
    </div>

    @if (!$profile)
        <x-owner.no-profile-warning message="Silakan isi profil toko Anda terlebih dahulu sebelum mengkustomisasi lokasi toko di peta." />
    @else
        <div class="grid gap-8 lg:grid-cols-3 max-w-6xl">
            {{-- Map Container Card --}}
            <div id="tour-map-wrapper" class="lg:col-span-2 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <div class="mb-3 flex items-center justify-between px-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Peta Desa Wisata</span>
                    <span class="text-xs font-medium text-primary">Klik di mana saja pada peta atau geser pin untuk memperbarui
                        lokasi</span>
                </div>
                <div id="location-map" class="border border-gray-100 shadow-inner relative">
                    <x-map-style-fab size="sm" class="absolute bottom-3 right-3 z-1000" />
                </div>
            </div>

            {{-- Form Controls Card --}}
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm self-start">
                <h3 class="font-display text-lg font-bold text-charcoal mb-4">Detail Koordinat</h3>

                <form method="POST" action="{{ route('owner.location.update') }}" x-data="{ locale: 'en' }">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        {{-- Locale tabs --}}
                        <x-locale-toggle />

                        {{-- Latitude --}}
                        <div id="tour-coords">
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
                            <div x-show="locale === 'en'">
                                <textarea name="accessibility_notes[en]" rows="3"
                                    placeholder="e.g. Ramp entrance at the main gate."
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">{{ old('accessibility_notes.en', $location ? ($location->getTranslation('accessibility_notes', 'en', false) ?? '') : '') }}</textarea>
                            </div>
                            <div x-show="locale === 'id'">
                                <textarea name="accessibility_notes[id]" rows="3"
                                    placeholder="Contoh: Tersedia ramp landai di pintu masuk utama."
                                    class="mt-1.5 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20">{{ old('accessibility_notes.id', $location ? ($location->getTranslation('accessibility_notes', 'id', false) ?? '') : '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 border-t border-gray-100 pt-4">
                        <button id="tour-save-btn" type="submit"
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

<x-map-style-modal />

@push('scripts')
    @if ($profile)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        @include('components.map-style-script')
        <script>
            const PENGLIPURAN_LAT = {{ config('services.penglipuran.latitude') }};
            const PENGLIPURAN_LNG = {{ config('services.penglipuran.longitude') }};
            const PENGLIPURAN_ZOOM = {{ config('services.penglipuran.zoom') }};

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

                initMapStyleSwitcher(map);

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

        <script>
            function startTutorial() {
                const driver = window.driver.js.driver;
                const steps = [];

                // Langkah 1: Pengantar
                steps.push({
                    element: '#tour-header',
                    popover: {
                        title: '👋 Selamat Datang!',
                        description: 'Panduan ini akan menunjukkan cara mengatur titik lokasi toko Anda di peta agar mudah ditemukan wisatawan.',
                        side: 'bottom',
                        align: 'start'
                    }
                });

                // Langkah 2: Peta interaktif
                if (document.getElementById('tour-map-wrapper') !== null) {
                    steps.push({
                        element: '#tour-map-wrapper',
                        popover: {
                            title: '🗺️ Peta Desa Wisata',
                            description: 'Klik di titik mana saja pada peta, atau geser pin ungu, untuk menandai lokasi toko Anda secara akurat.',
                            side: 'top',
                            align: 'start'
                        }
                    });
                }

                // Langkah 3: Koordinat otomatis
                if (document.getElementById('tour-coords') !== null) {
                    steps.push({
                        element: '#tour-coords',
                        popover: {
                            title: '📍 Koordinat Otomatis',
                            description: 'Latitude dan longitude akan terisi otomatis setiap kali Anda mengklik peta atau menggeser pin. Anda tidak perlu mengisinya secara manual.',
                            side: 'right',
                            align: 'start'
                        }
                    });
                }

                // Langkah 4: Tombol Simpan
                if (document.getElementById('tour-save-btn') !== null) {
                    steps.push({
                        element: '#tour-save-btn',
                        popover: {
                            title: '💾 Simpan Lokasi',
                            description: 'Setelah pin berada di posisi yang tepat, klik tombol ini untuk menyimpan lokasi toko Anda.',
                            side: 'top',
                            align: 'start'
                        }
                    });
                }

                const driverObj = driver({
                    showProgress: true,
                    allowClose: true,
                    steps: steps,
                    popoverClass: 'driverjs-theme'
                });

                driverObj.drive();
            }

            // Auto-run for first-time visitors
            document.addEventListener('DOMContentLoaded', () => {
                const tourCompleted = localStorage.getItem('owner_location_tour_completed');
                if (!tourCompleted) {
                    // Delay slightly to allow the Leaflet map to finish initializing
                    setTimeout(() => {
                        startTutorial();
                        localStorage.setItem('owner_location_tour_completed', 'true');
                    }, 1000);
                }
            });
        </script>
    @endif
@endpush