@extends('layouts.dashboard')

@section('title', 'Peta Lokasi & Titik')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-control-attribution {
            display: none !important;
        }

        /* Premium Selected Marker Animation */
        @keyframes pin-breath {
            0%, 100% {
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

        @media (min-width: 1024px) {
            #admin-main {
                height: 100vh;
                overflow: hidden !important;
                display: flex;
                flex-direction: column;
                padding-bottom: 2rem !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div id="tour-header">
            <h1 class="font-display text-2xl font-bold text-charcoal">Peta Lokasi & Titik</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola objek budaya, UMKM, dan fasilitas desa langsung di atas peta interaktif.</p>
        </div>
        <div class="flex items-center gap-2">
            <button id="tour-trigger-btn" onclick="startTutorial()"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:bg-gray-100 active:scale-[0.98]"
                title="Panduan Interaktif">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:flex-1 lg:min-h-0">

        {{-- Left Side Panel: Instructions, Filters, and Dynamic Forms --}}
        <div id="tour-side-panel" class="lg:col-span-4 lg:h-full lg:overflow-y-auto lg:pr-2 flex flex-col space-y-4">
            @include('admin.map-manager.partials.idle-panel')
            @include('admin.map-manager.partials.editor-panel')
        </div>

        {{-- Right Side Panel: The Interactive Map --}}
        <div id="tour-map-panel" class="lg:col-span-8 lg:h-full">
            @include('admin.map-manager.partials.map-panel')
        </div>

    </div>
@endsection

<x-map-style-modal />

@push('scripts')
    @include('admin.map-manager.partials.scripts')
    <x-tiptap-editor-script />
    <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>

    <script>
        function startTutorial() {
            const driver = window.driver.js.driver;
            const hasStyleSwitcher = document.getElementById('btn-map-style') !== null;
            const steps = [];

            // Langkah 1: Pengantar
            steps.push({
                element: '#tour-header',
                popover: {
                    title: '👋 Selamat Datang!',
                    description: 'Panduan ini akan menunjukkan cara mengelola lokasi objek budaya, UMKM, dan fasilitas langsung di atas peta interaktif.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Panel Navigasi & Filter
            steps.push({
                element: '#tour-side-panel',
                popover: {
                    title: '🧭 Panel Navigasi',
                    description: 'Panel ini menampilkan panduan singkat dan akan berubah menjadi formulir saat Anda menambah atau mengedit sebuah lokasi.',
                    side: 'right',
                    align: 'start'
                }
            });

            // Langkah 3: Filter Kategori
            steps.push({
                element: '#tour-filters',
                popover: {
                    title: '🎛️ Filter Kategori Peta',
                    description: 'Centang atau hilangkan centang kategori di sini untuk menyaring penanda yang ditampilkan di peta.',
                    side: 'right',
                    align: 'start'
                }
            });

            // Langkah 4: Peta Interaktif
            steps.push({
                element: '#tour-map',
                popover: {
                    title: '🗺️ Peta Interaktif',
                    description: 'Klik area kosong pada peta untuk menambahkan lokasi baru, atau klik penanda yang sudah ada untuk melihat dan mengubah detailnya.',
                    side: 'left',
                    align: 'start'
                }
            });

            if (hasStyleSwitcher) {
                // Langkah 5: Ganti Tampilan Peta
                steps.push({
                    element: '#btn-map-style',
                    popover: {
                        title: '🛰️ Ganti Tampilan Peta',
                        description: 'Gunakan tombol ini untuk beralih antara tampilan peta standar dan satelit.',
                        side: 'top',
                        align: 'end'
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
            const tourCompleted = localStorage.getItem('admin_map_manager_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('admin_map_manager_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush