@extends('layouts.app')
@section('title', 'Rekomendasi UMKM - Penglipuran')
@section('header_title', 'Rekomendasi UMKM')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 250px;
            width: 100%;
            border-radius: 0.75rem;
            z-index: 10;
        }

        .custom-div-icon {
            background: transparent;
            border: none;
        }

        .marker-pin {
            width: 30px;
            height: 30px;
            border-radius: 50% 50% 50% 0;
            background: #F97316;
            position: absolute;
            transform: rotate(-45deg);
            left: 50%;
            top: 50%;
            margin: -15px 0 0 -15px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
        }

        .marker-pin::after {
            content: '';
            width: 14px;
            height: 14px;
            background: #fff;
            border-radius: 50%;
        }
    </style>
    <div class="relative pb-32">
        <!-- Confetti / Success Header -->
        <div class="bg-primary/10 relative w-full overflow-hidden px-4 py-10 text-center">
            <!-- Confetti icons as background decor -->
            <div class="text-primary absolute left-4 top-4 rotate-12 opacity-20">
                <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                </svg>
            </div>
            <div class="text-primary absolute bottom-4 right-4 -rotate-12 opacity-20">
                <svg class="h-10 w-10" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                </svg>
            </div>

            <div
                class="text-primary mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white shadow-md">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-charcoal text-2xl font-bold">Hore! Kami Menemukannya!</h1>
            <p class="mx-auto mt-2 max-w-xs text-sm text-gray-600">Kami telah mencarikan UMKM terbaik yang memiliki semua
                pesanan Anda.</p>
        </div>

        <!-- Info Card -->
        <div class="relative z-10 -mt-4 rounded-t-3xl border-b border-gray-100 bg-white px-4 py-6 shadow-sm">
            <div class="mb-4 flex items-center gap-4">
                <div
                    class="text-primary flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gray-100 shadow-inner">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-charcoal text-xl font-bold">{{ $umkm->business_name }}</h2>
                    <p class="mt-1 flex items-center gap-1 text-sm text-gray-500">
                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Milik: {{ $umkm->owner_name }}
                    </p>
                    <div class="mt-1.5 flex items-center gap-2">
                        <span class="text-accent flex items-center text-xs font-medium">
                            <svg class="mr-1 h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            {{ $umkm->rating ?? '4.8' }}
                        </span>
                        <span
                            class="bg-primary/10 text-primary rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider">
                            Rekomendasi
                        </span>
                    </div>
                </div>
            </div>
            @if ($umkm->description)
                <p class="mt-2 text-sm text-gray-600">{{ $umkm->description }}</p>
            @endif
        </div>

        <!-- Route Guidance / Embedded Map -->
        <div class="mt-2 border-y border-gray-100 bg-white px-4 py-5" id="map-section">
            <h3 class="text-charcoal mb-3 flex items-center gap-2 font-bold">
                <svg class="text-primary h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Lokasi UMKM
            </h3>

            <div class="rounded-xl border border-gray-100 bg-gray-50 p-2">
                <div id="map"></div>
            </div>
            <p class="mt-2 text-center text-xs text-gray-500">Peta ini menunjukkan lokasi UMKM di Desa Penglipuran.</p>
        </div>

        <!-- Products Preview -->
        <div class="mt-2 border-t border-gray-100 bg-white px-4 py-5" x-data>
            <h3 class="text-charcoal mb-3 font-bold">Produk yang Tersedia</h3>
            <div class="space-y-3">
                @forelse($umkm->activeProducts as $product)
                    <div @click="$dispatch('open-product-modal', {{ json_encode([
                        'name' => $product->name,
                        'category' => $product->category->name ?? 'Produk',
                        'price' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                        'image' => $product->image_path ? asset('storage/' . $product->image_path) : '',
                        'description' => $product->description ?? 'Tidak ada deskripsi.',
                    ]) }})"
                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-100 p-3 transition-colors active:bg-gray-50">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-100 text-gray-400">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}"
                                    class="h-full w-full object-cover">
                            @else
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="text-charcoal line-clamp-1 text-sm font-bold">{{ $product->name }}</h4>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $product->category->name ?? 'Produk' }}</p>
                        </div>
                        <div class="text-primary text-sm font-bold">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-gray-500">UMKM ini tidak memiliki produk aktif saat ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sticky Bottom CTA -->
    <div
        class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-100 bg-white/90 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-8px_20px_rgba(0,0,0,0.06)] backdrop-blur-md">
        @if ($umkm->mapLocation)
            <a href="{{ route('explore', [
                'lat' => $umkm->mapLocation->latitude,
                'lng' => $umkm->mapLocation->longitude,
                'name' => $umkm->business_name,
                'action' => 'route',
            ]) }}"
                class="bg-primary flex h-12 w-full items-center justify-center gap-2 rounded-xl font-bold text-white transition-all active:scale-[0.98]">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Lihat Peta untuk Bayar di Tempat
            </a>
        @else
            <button
                class="bg-primary flex h-12 w-full items-center justify-center gap-2 rounded-xl font-bold text-white transition-all active:scale-[0.98]"
                onclick="scrollToMap()">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Lihat Peta untuk Bayar di Tempat
            </button>
        @endif
        <p class="mt-2 text-center text-xs text-gray-500">UMKM ini melayani pembayaran langsung di lokasi (Bayar di
            Tempat).</p>
    </div>
    <!-- Product Detail Modal -->
    <div x-data="{ selectedProduct: null }" @open-product-modal.window="selectedProduct = $event.detail">
        <x-modal name="product-modal" maxWidth="sm">
            <!-- Image Header -->
            <div class="relative -mx-6 -mt-6 mb-6 h-48 overflow-hidden rounded-t-[2.5rem] bg-gray-100 md:rounded-t-3xl">
                <template x-if="selectedProduct?.image">
                    <img :src="selectedProduct.image" class="h-full w-full object-cover" alt="Product Image">
                </template>
                <template x-if="!selectedProduct?.image">
                    <div class="flex h-full w-full items-center justify-center text-gray-300">
                        <svg class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </template>
                <!-- Close Button on Mobile -->
                <button type="button" @click="isOpen = false"
                    class="absolute right-4 top-4 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:text-gray-600 active:scale-95 md:hidden"
                    title="Tutup">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-1">
                <div class="mb-2 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-charcoal text-xl font-bold leading-tight" x-text="selectedProduct?.name"></h3>
                        <p class="mt-1 text-sm text-gray-500" x-text="selectedProduct?.category"></p>
                    </div>
                    <div class="text-primary shrink-0 text-lg font-bold" x-text="selectedProduct?.price"></div>
                </div>
                <div class="my-4 h-px w-full bg-gray-100"></div>
                <p class="text-sm leading-relaxed text-gray-600" x-text="selectedProduct?.description"></p>
            </div>
        </x-modal>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        (function() {
            let mapInstance = null;

            const handleRecommendedLoad = function(evt) {
                const container = evt.detail.elt;
                
                // 1. Confetti trigger
                const hasConfetti = container.querySelector('.bg-primary/10') || (container.classList && container.classList.contains('bg-primary/10'));
                if (hasConfetti) {
                    var duration = 3 * 1000;
                    var animationEnd = Date.now() + duration;
                    var defaults = {
                        startVelocity: 30,
                        spread: 360,
                        ticks: 60,
                        zIndex: 100
                    };

                    function randomInRange(min, max) {
                        return Math.random() * (max - min) + min;
                    }

                    var interval = setInterval(function() {
                        var timeLeft = animationEnd - Date.now();

                        if (timeLeft <= 0) {
                            return clearInterval(interval);
                        }

                        var particleCount = 50 * (timeLeft / duration);
                        confetti(Object.assign({}, defaults, {
                            particleCount,
                            origin: {
                                x: randomInRange(0.1, 0.3),
                                y: Math.random() - 0.2
                            }
                        }));
                        confetti(Object.assign({}, defaults, {
                            particleCount,
                            origin: {
                                x: randomInRange(0.7, 0.9),
                                y: Math.random() - 0.2
                            }
                        }));
                    }, 250);
                }

                // 2. Map trigger
                const mapEl = container.querySelector('#map') || (container.id === 'map' ? container : null);
                if (mapEl && !mapInstance) {
                    @if ($umkm->mapLocation)
                        const lat = {{ $umkm->mapLocation->latitude }};
                        const lng = {{ $umkm->mapLocation->longitude }};

                        mapInstance = L.map(mapEl, {
                            zoomControl: false,
                            attributionControl: false
                        }).setView([lat, lng], 17);

                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            maxZoom: 20
                        }).addTo(mapInstance);

                        const customIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: `<div class="marker-pin"></div>`,
                            iconSize: [30, 42],
                            iconAnchor: [15, 42]
                        });

                        L.marker([lat, lng], {
                                icon: customIcon
                            })
                            .bindPopup(`<b>{{ $umkm->business_name }}</b>`)
                            .addTo(mapInstance);
                    @endif
                }
            };

            document.body.addEventListener('htmx:load', handleRecommendedLoad);

            document.addEventListener('htmx:beforeSwap', function cleanup(e) {
                if (mapInstance) {
                    mapInstance.remove();
                    mapInstance = null;
                }
                document.body.removeEventListener('htmx:load', handleRecommendedLoad);
                document.removeEventListener('htmx:beforeSwap', cleanup);
            });
        })();

        function scrollToMap() {
            if (navigator.vibrate) navigator.vibrate(50);
            const mapSec = document.getElementById('map-section');
            if (mapSec) {
                mapSec.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        }
    </script>
@endsection
