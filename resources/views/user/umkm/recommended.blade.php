@extends('layouts.app')
@section('title', 'Rekomendasi UMKM - Penglipuran')
@section('header_title', 'Rekomendasi UMKM')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 250px; width: 100%; border-radius: 0.75rem; z-index: 10; }
    .custom-div-icon { background: transparent; border: none; }
    .marker-pin {
        width: 30px; height: 30px; border-radius: 50% 50% 50% 0; background: #F97316;
        position: absolute; transform: rotate(-45deg); left: 50%; top: 50%; margin: -15px 0 0 -15px;
        display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 6px rgba(0,0,0,0.3);
    }
    .marker-pin::after {
        content: ''; width: 14px; height: 14px; background: #fff; border-radius: 50%;
    }
</style>
@endpush

@section('content')
    <div class="relative pb-32">
        <!-- Confetti / Success Header -->
        <div class="w-full bg-primary/10 py-10 px-4 text-center relative overflow-hidden">
            <!-- Confetti icons as background decor -->
            <div class="absolute top-4 left-4 text-primary opacity-20 rotate-12">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <div class="absolute bottom-4 right-4 text-primary opacity-20 -rotate-12">
                <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>

            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full text-primary mb-4 shadow-md">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-charcoal">Hore! Kami Menemukannya!</h1>
            <p class="text-sm text-gray-600 mt-2 max-w-xs mx-auto">Kami telah mencarikan UMKM terbaik yang memiliki semua pesanan Anda.</p>
        </div>

        <!-- Info Card -->
        <div class="px-4 py-6 bg-white -mt-4 rounded-t-3xl relative z-10 border-b border-gray-100 shadow-sm">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center text-primary shrink-0 shadow-inner">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-charcoal">{{ $umkm->business_name }}</h2>
                    <p class="text-sm text-gray-500 mt-1 flex items-center gap-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Milik: {{ $umkm->owner_name }}
                    </p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-accent flex items-center text-xs font-medium">
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            {{ $umkm->rating ?? '4.8' }}
                        </span>
                        <span class="bg-primary/10 text-primary text-[10px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">
                            Rekomendasi
                        </span>
                    </div>
                </div>
            </div>
            @if($umkm->description)
                <p class="text-sm text-gray-600 mt-2">{{ $umkm->description }}</p>
            @endif
        </div>

        <!-- Route Guidance / Embedded Map -->
        <div class="px-4 py-5 bg-white mt-2 border-y border-gray-100" id="map-section">
            <h3 class="font-bold text-charcoal mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Lokasi UMKM
            </h3>
            
            <div class="bg-gray-50 rounded-xl p-2 border border-gray-100">
                <div id="map"></div>
            </div>
            <p class="text-xs text-gray-500 mt-2 text-center">Peta ini menunjukkan lokasi UMKM di Desa Penglipuran.</p>
        </div>

        <!-- Products Preview -->
        <div class="px-4 py-5 bg-white mt-2 border-t border-gray-100" x-data>
            <h3 class="font-bold text-charcoal mb-3">Produk yang Tersedia</h3>
            <div class="space-y-3">
                @forelse($umkm->activeProducts as $product)
                    <div @click="$dispatch('open-product-modal', {{ json_encode([
                            'name' => $product->name,
                            'category' => $product->category->name ?? 'Produk',
                            'price' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                            'image' => $product->image_path ? asset('storage/'.$product->image_path) : '',
                            'description' => $product->description ?? 'Tidak ada deskripsi.'
                        ]) }})" 
                        class="flex items-center gap-3 p-3 border border-gray-100 rounded-xl cursor-pointer active:bg-gray-50 transition-colors">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 shrink-0 overflow-hidden">
                            @if($product->image_path)
                                <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-charcoal line-clamp-1">{{ $product->name }}</h4>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $product->category->name ?? 'Produk' }}</p>
                        </div>
                        <div class="text-sm font-bold text-primary">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-4">UMKM ini tidak memiliki produk aktif saat ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sticky Bottom CTA -->
    <div class="fixed bottom-0 pb-[calc(1rem+env(safe-area-inset-bottom))] inset-x-0 p-4 bg-white/90 backdrop-blur-md border-t border-gray-100 z-30 shadow-[0_-8px_20px_rgba(0,0,0,0.06)]">
        <button class="w-full bg-primary text-white font-bold h-12 rounded-xl active:scale-[0.98] transition-all flex items-center justify-center gap-2" onclick="scrollToMap()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Lihat Peta untuk Bayar di Tempat
        </button>
        <p class="text-xs text-center text-gray-500 mt-2">UMKM ini melayani pembayaran langsung di lokasi (Bayar di Tempat).</p>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function scrollToMap() {
        if(navigator.vibrate) navigator.vibrate(50);
        document.getElementById('map-section').scrollIntoView({ behavior: 'smooth' });
    }

    @if($umkm->mapLocation)
        const lat = {{ $umkm->mapLocation->latitude }};
        const lng = {{ $umkm->mapLocation->longitude }};
        
        const map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([lat, lng], 17);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 20
        }).addTo(map);

        const customIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div class="marker-pin"></div>`,
            iconSize: [30, 42],
            iconAnchor: [15, 42]
        });

        L.marker([lat, lng], {icon: customIcon})
            .bindPopup(`<b>{{ $umkm->business_name }}</b>`)
            .addTo(map);
    @endif
</script>
@endpush

@push('modals')
    <!-- Product Detail Modal -->
    <div x-data="{ selectedProduct: null }" @open-product-modal.window="selectedProduct = $event.detail">
        <x-modal name="product-modal" maxWidth="sm">
            <!-- Image Header -->
            <div class="h-48 bg-gray-100 relative -mx-6 -mt-6 mb-6 rounded-t-[2.5rem] overflow-hidden md:rounded-t-3xl">
                <template x-if="selectedProduct?.image">
                    <img :src="selectedProduct.image" class="w-full h-full object-cover" alt="Product Image">
                </template>
                <template x-if="!selectedProduct?.image">
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </template>
                <!-- Close Button on Mobile -->
                <button @click="isOpen = false" class="md:hidden absolute top-4 right-4 w-8 h-8 bg-black/30 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-black/50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <!-- Content -->
            <div class="p-1">
                <div class="flex justify-between items-start gap-4 mb-2">
                    <div>
                        <h3 class="text-xl font-bold text-charcoal leading-tight" x-text="selectedProduct?.name"></h3>
                        <p class="text-sm text-gray-500 mt-1" x-text="selectedProduct?.category"></p>
                    </div>
                    <div class="text-lg font-bold text-primary shrink-0" x-text="selectedProduct?.price"></div>
                </div>
                <div class="w-full h-px bg-gray-100 my-4"></div>
                <p class="text-sm text-gray-600 leading-relaxed" x-text="selectedProduct?.description"></p>
            </div>
        </x-modal>
    </div>
@endpush
