@extends('layouts.dashboard')

@section('title', 'Tambah Rute Wisata')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .leaflet-control-attribution { display: none !important; }
        .custom-pin-selected {
            transition: all 0.3s ease;
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

<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.tour-routes') }}" class="rounded-xl p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-charcoal">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Tambah Rute Wisata Baru</h1>
        <p class="mt-0.5 text-sm text-gray-500">Pilih titik kunjungan pada peta secara berurutan untuk membuat rute perjalanan.</p>
    </div>
</div>

<form action="{{ route('admin.tour-routes.store') }}" method="POST" id="route-form" class="lg:flex-1 lg:flex lg:flex-col lg:min-h-0">
    @csrf
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:flex-1 lg:min-h-0">
        
        {{-- Left Form Panel (Metadata & Selected Points) --}}
        <div class="lg:col-span-5 space-y-6 lg:h-full lg:overflow-y-auto lg:pr-4">
            @include('admin.tour-routes.partials.create-info')
            @include('admin.tour-routes.partials.metrics')
            @include('admin.tour-routes.partials.selected-points')

            {{-- Actions --}}
            <div class="flex gap-3">
                <button type="submit"
                    class="flex-1 rounded-xl bg-primary py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                    Simpan Rute Wisata
                </button>
                <a href="{{ route('admin.tour-routes') }}"
                    class="rounded-xl border border-gray-200 px-6 py-3 text-center text-sm font-semibold text-gray-500 transition-all hover:bg-gray-50">
                    Batal
                </a>
            </div>
        </div>

        {{-- Right Map Panel (Sticky Map) --}}
        @include('admin.tour-routes.partials.map-panel')

    </div>
</form>

@endsection

<x-map-style-modal />
@include('admin.tour-routes.partials.mission-modal')

@push('scripts')
    @include('admin.tour-routes.partials.scripts')
    @include('admin.tour-routes.partials.scripts.missions')
@endpush
