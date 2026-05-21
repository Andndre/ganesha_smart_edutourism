@extends('layouts.app')

@section('title', 'Peta Interaktif - Penglipuran Smart Tour')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        /* Sembunyikan atribusi leaflet yang terlalu besar di HP */
        .leaflet-control-attribution {
            display: none !important;
        }

        /* Hilangkan efek outline saat klik marker */
        .leaflet-container:focus {
            outline: none;
        }

        /* Animasi Bottom Sheet */
        .bottom-sheet-enter {
            transform: translateY(100%);
        }

        .bottom-sheet-active {
            transform: translateY(0);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
@endpush

@section('content')
    <div class="fixed inset-x-0 bottom-0 top-14 z-0 overflow-hidden bg-[#E5E3DF]">
        <div id="map" class="absolute inset-0 z-0"></div>

        @include('pages.explore.components.map-search')
        @include('pages.explore.components.map-fab')
        @include('pages.explore.components.location-sheet')
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Inisialisasi Peta (Koordinat Desa Penglipuran: -8.4216, 115.3588)
            const map = L.map('map', {
                zoomControl: false // Sembunyikan zoom control bawaan (kita buat UI khusus mobile)
            }).setView([-8.4216, 115.3588], 17);

            // 2. Tambahkan Tile Layer (Gunakan CartoDB Positron agar desain bersih/minimalis)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                maxZoom: 20,
            }).addTo(map);

            // 3. Data Mockup (Nanti diganti dengan fetch dari API /api/map/locations)
            const locations = [{
                    lat: -8.4216,
                    lng: 115.3588,
                    name: "Pura Penataran",
                    cat: "Budaya",
                    desc: "Kawasan suci utama desa. Harap berpakaian sopan saat memasuki area ini."
                },
                {
                    lat: -8.4230,
                    lng: 115.3585,
                    name: "Hutan Bambu",
                    cat: "Edukasi",
                    desc: "Hutan bambu seluas 45 hektar yang melestarikan keseimbangan ekosistem."
                },
                {
                    lat: -8.4225,
                    lng: 115.3589,
                    name: "Kopi Luwak Pak Wayan",
                    cat: "UMKM",
                    desc: "Kedai kopi otentik dengan pemandangan langsung ke arsitektur rumah tradisional."
                }
            ];

            // 4. Custom Icon Sederhana menggunakan HTML/DivIcon
            const customIcon = L.divIcon({
                className: 'custom-pin',
                html: `<div style="background-color: #1E5128; width: 24px; height: 24px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });

            // 5. Render Marker ke Peta
            locations.forEach(loc => {
                const marker = L.marker([loc.lat, loc.lng], {
                    icon: customIcon
                }).addTo(map);

                // Tambahkan event klik pada marker
                marker.on('click', function() {
                    openSheet(loc.name, loc.cat, loc.desc);
                    map.flyTo([loc.lat - 0.0005, loc.lng], 18, {
                        animate: true,
                        duration: 0.5
                    }); // Geser peta sedikit agar marker tidak tertutup bottom sheet
                });
            });
        });

        // ==========================================
        // LOGIKA BOTTOM SHEET
        // ==========================================
        const sheet = document.getElementById('location-sheet');

        function openSheet(name, category, desc) {
            // Update konten HTML
            document.getElementById('sheet-title').textContent = name;
            document.getElementById('sheet-category').textContent = category;
            document.getElementById('sheet-desc').textContent = desc;

            // Tampilkan animasi naik
            sheet.classList.remove('translate-y-full');
            sheet.classList.add('translate-y-0');
        }

        function closeSheet() {
            // Sembunyikan dengan animasi turun
            sheet.classList.remove('translate-y-0');
            sheet.classList.add('translate-y-full');
        }
    </script>
@endpush
