<div class="absolute bottom-24 right-4 z-10 flex flex-col gap-3">
    <!-- Map Style Button (Jenis Peta) -->
    <x-map-style-fab />

    {{-- "Wisatawan Live" & "Heatmap" pindah ke modal Jenis Peta (map-layers.blade.php):
         keduanya overlay, dan lima FAB bertumpuk menutupi peta terus-menerus. --}}
    @if(app()->isLocal())
    <!-- Mock GPS Button -->
    <button id="btn-mock-gps"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all active:scale-95"
        aria-label="Mock GPS (Dev Only)" title="Mock GPS (Dev Only)">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
        </svg>
    </button>
    @endif

    <!-- My Location Button -->
    <button id="btn-my-location"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-all active:scale-95"
        aria-label="{{ __('Lokasi Saya') }}" title="{{ __('Lokasi Saya') }}">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 4v4m0 8v4M4 12h4m8 0h4m-4 0a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    </button>

    <!-- Locate Button -->
    <button id="btn-locate"
        class="tap-target flex h-12 w-12 items-center justify-center rounded-full border border-gray-100 bg-white text-gray-600 shadow-md transition-transform active:scale-95"
        aria-label="{{ __('Lihat Semua Lokasi') }}" title="{{ __('Lihat Semua Lokasi') }}">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
        </svg>
    </button>
</div>
