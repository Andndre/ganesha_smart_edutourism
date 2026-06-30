<div class="absolute bottom-24 right-4 z-10 flex flex-col gap-3">
    <!-- Map Style Button (Jenis Peta) -->
    <button id="btn-map-style"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all active:scale-95"
        title="{{ __('Jenis Peta') }}">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4zm10 0h6v6h-6zM4 14h6v6H4zm10 0h6v6h-6z" />
        </svg>
    </button>

    <!-- Layer Map Button (Wisatawan Live) -->
    <button id="btn-layer-map"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all active:scale-95"
        title="{{ __('Wisatawan Live') }}">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
    </button>

    <!-- Real Heatmap Button -->
    <button id="btn-real-heatmap"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all active:scale-95"
        title="{{ __('Kepadatan Panas (Heatmap)') }}">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" />
        </svg>
    </button>
    @if(app()->isLocal())
    <!-- Mock GPS Button -->
    <button id="btn-mock-gps"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all active:scale-95"
        title="Mock GPS (Dev Only)">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
        </svg>
    </button>
    @endif

    <!-- My Location Button -->
    <button id="btn-my-location"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all active:scale-95"
        title="{{ __('Lokasi Saya') }}">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 4v4m0 8v4M4 12h4m8 0h4m-4 0a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    </button>

    <!-- Locate Button -->
    <button id="btn-locate"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-transform active:scale-95"
        title="{{ __('Lihat Semua Lokasi') }}">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
        </svg>
    </button>
</div>
