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
    <div class="mb-6">
        <h1 class="font-display text-2xl font-bold text-charcoal">Peta Lokasi & Titik</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola objek budaya, UMKM, dan fasilitas desa langsung di atas peta interaktif.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:flex-1 lg:min-h-0">

        {{-- Left Side Panel: Instructions, Filters, and Dynamic Forms --}}
        <div class="lg:col-span-4 lg:h-full lg:overflow-y-auto lg:pr-2 flex flex-col space-y-4">
            @include('admin.map-manager.partials.idle-panel')
            @include('admin.map-manager.partials.editor-panel')
        </div>

        {{-- Right Side Panel: The Interactive Map --}}
        <div class="lg:col-span-8 lg:h-full">
            @include('admin.map-manager.partials.map-panel')
        </div>

    </div>
@endsection

@push('scripts')
    @include('admin.map-manager.partials.scripts')
@endpush