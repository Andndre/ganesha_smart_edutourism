@extends('layouts.app')
@section('title', 'Rute Belanja UMKM - Penglipuran')
@section('header_title', 'Rute Belanja UMKM')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 300px; width: 100%; border-radius: 1rem; z-index: 10; }
    .custom-div-icon {
        background: transparent;
        border: none;
    }
    .marker-pin {
        width: 30px;
        height: 30px;
        border-radius: 50% 50% 50% 0;
        background: #F97316; /* Primary color */
        position: absolute;
        transform: rotate(-45deg);
        left: 50%;
        top: 50%;
        margin: -15px 0 0 -15px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 3px 6px rgba(0,0,0,0.3);
    }
    .marker-pin::after {
        content: '';
        width: 14px;
        height: 14px;
        background: #fff;
        border-radius: 50%;
    }
    .marker-number {
        position: absolute;
        width: 22px;
        height: 22px;
        left: 50%;
        top: 50%;
        margin: -11px 0 0 -11px;
        background: white;
        border-radius: 50%;
        text-align: center;
        color: #F97316;
        font-weight: bold;
        font-size: 12px;
        line-height: 22px;
        z-index: 1;
        box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    }
</style>
@endpush

@section('content')
    @php $totalPrice = 0; @endphp
    <div class="px-4 pt-[calc(env(safe-area-inset-top)+6rem)] pb-32">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-charcoal">Rute Belanja Anda</h2>
            <p class="text-sm text-gray-500 mt-1">Kami telah menemukan beberapa UMKM terdekat agar Anda mendapatkan semua pesanan Anda.</p>
        </div>

        <!-- Map Container -->
        <div class="bg-white rounded-2xl p-2 border border-gray-100 shadow-sm mb-6">
            <div id="map"></div>
        </div>

        <!-- Itinerary / Route Steps -->
        <div class="space-y-4 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-linear-to-b before:from-transparent before:via-slate-200 before:to-transparent">
            @foreach($route as $index => $stop)
            @php 
                $umkm = $stop['umkm']; 
                if (is_object($umkm) && !($umkm instanceof \App\Models\UmkmProfile)) {
                    $umkm = json_decode(json_encode($umkm), true);
                }
                if (is_array($umkm)) {
                    $umkmModel = new \App\Models\UmkmProfile();
                    $umkmModel->exists = true;
                    $umkmModel->forceFill($umkm);
                    if (isset($umkm['products'])) {
                        $products = collect($umkm['products'])->map(function($p) {
                            return (new \App\Models\UmkmProduct())->forceFill($p);
                        });
                        $umkmModel->setRelation('products', $products);
                        $umkmModel->setRelation('activeProducts', $products);
                    }
                    if (isset($umkm['map_location'])) {
                        $loc = (new \App\Models\MapLocation())->forceFill($umkm['map_location']);
                        $umkmModel->setRelation('mapLocation', $loc);
                    }
                    $umkm = $umkmModel;
                }
            @endphp
            <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                <!-- Icon -->
                <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-primary text-white shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                    <span class="font-bold">{{ $index + 1 }}</span>
                </div>
                <!-- Card -->
                <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] p-4 rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h3 class="font-bold text-charcoal text-base">{{ $umkm->business_name }}</h3>
                            <p class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $umkm->owner_name }}
                            </p>
                        </div>
                        <a href="{{ route('umkm.recommended', $umkm->id) }}" class="text-primary hover:bg-primary/10 p-2 rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Beli di sini:</p>
                        <div class="space-y-2">
                            @php
                                $stopCategoryIds = collect($stop['categories'])->map(function($c) {
                                    return is_array($c) ? ($c['id'] ?? $c) : (is_object($c) ? ($c->id ?? $c) : $c);
                                })->toArray();
                            @endphp
                            @foreach($umkm->activeProducts as $product)
                                @if(in_array($product->umkm_product_category_id, $stopCategoryIds))
                                @php $totalPrice += $product->price; @endphp
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-charcoal">{{ $product->name }}</span>
                                    <span class="font-bold text-primary">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Sticky Bottom CTA -->
    <div class="fixed bottom-0 pb-[calc(1rem+env(safe-area-inset-bottom))] inset-x-0 p-4 bg-white/90 backdrop-blur-md border-t border-gray-100 z-30 shadow-[0_-8px_20px_rgba(0,0,0,0.06)]">
        <div class="flex justify-between items-center mb-3">
            <span class="text-sm font-semibold text-gray-500">Total Estimasi Belanja</span>
            <span class="text-base font-extrabold text-primary font-display">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
        </div>
        <button class="w-full bg-primary text-white font-bold h-12 rounded-xl active:scale-[0.98] transition-all flex items-center justify-center gap-2" onclick="startNavigation()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            Mulai Perjalanan
        </button>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const routeData = @json($route);
    const mapCoordinates = routeData.map(stop => [stop.umkm.map_location.latitude, stop.umkm.map_location.longitude]);

    // Initialize Map
    const map = L.map('map', {
        zoomControl: false,
        attributionControl: false
    });

    // Add CartoDB Positron tiles for a clean look
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        maxZoom: 20
    }).addTo(map);

    // Add markers
    const bounds = L.latLngBounds();
    
    routeData.forEach((stop, index) => {
        const lat = stop.umkm.map_location.latitude;
        const lng = stop.umkm.map_location.longitude;
        bounds.extend([lat, lng]);

        const iconHtml = `
            <div class="marker-pin"></div>
            <div class="marker-number">${index + 1}</div>
        `;
        
        const customIcon = L.divIcon({
            className: 'custom-div-icon',
            html: iconHtml,
            iconSize: [30, 42],
            iconAnchor: [15, 42]
        });

        L.marker([lat, lng], {icon: customIcon})
            .bindPopup(`<b>${stop.umkm.business_name}</b>`)
            .addTo(map);
    });

    if (mapCoordinates.length > 0) {
        map.fitBounds(bounds, { padding: [30, 30] });
    }

    // Attempt to draw route using local OpenRouteService
    if (mapCoordinates.length >= 2) {
        // ORS expects [lng, lat]
        const orsCoordinates = mapCoordinates.map(coord => [parseFloat(coord[1]), parseFloat(coord[0])]);
        
        fetch('/api/routing/directions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ coordinates: orsCoordinates })
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.features && data.features.length > 0) {
                const geojson = data.features[0];
                L.geoJSON(geojson, {
                    style: {
                        color: '#F97316', // Primary color
                        weight: 4,
                        opacity: 0.8,
                        dashArray: '10, 10'
                    }
                }).addTo(map);
            } else {
                // Fallback: draw straight lines if routing fails
                drawStraightLines();
            }
        })
        .catch(err => {
            console.error('Routing failed:', err);
            drawStraightLines();
        });
    }

    function drawStraightLines() {
        L.polyline(mapCoordinates, {
            color: '#F97316',
            weight: 3,
            opacity: 0.6,
            dashArray: '5, 10'
        }).addTo(map);
    }

    function startNavigation() {
        if(navigator.vibrate) navigator.vibrate(50);
        // Simple alert for now, could integrate with real navigation
        alert("Mengaktifkan mode navigasi rute...");
    }
</script>
@endpush
