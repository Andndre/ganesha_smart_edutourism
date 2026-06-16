@extends('layouts.app')
@section('title', 'Rute Belanja UMKM - Penglipuran')
@section('header_title', 'Rute Belanja UMKM')

@push('styles')
    <style>
        #map {
            height: 300px;
            width: 100%;
            border-radius: 1rem;
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
            /* Primary color */
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
    </style>
@endpush

@section('content')
    @php $totalPrice = 0; @endphp
    <div class="px-4 pb-32 pt-6">
        <div class="mb-6">
            <h2 class="text-charcoal text-xl font-bold">Rute Belanja Anda</h2>
            <p class="mt-1 text-sm text-gray-500">Kami telah menemukan beberapa UMKM terdekat agar Anda mendapatkan semua
                pesanan Anda.</p>
        </div>

        <!-- Map Container -->
        <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-2 shadow-sm">
            <div id="map"></div>
        </div>

        <!-- Itinerary / Route Steps -->
        <div
            class="before:bg-linear-to-b relative space-y-4 before:absolute before:inset-0 before:ml-5 before:h-full before:w-0.5 before:-translate-x-px before:from-transparent before:via-slate-200 before:to-transparent md:before:mx-auto md:before:translate-x-0">
            @foreach ($route as $index => $stop)
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
                            $products = collect($umkm['products'])->map(function ($p) {
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
                <div
                    class="is-active group relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse">
                    <!-- Icon -->
                    <div
                        class="bg-primary z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white text-white shadow md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                        <span class="font-bold">{{ $index + 1 }}</span>
                    </div>
                    <!-- Card -->
                    <div
                        class="w-[calc(100%-4rem)] rounded-2xl border border-gray-100 bg-white p-4 shadow-sm md:w-[calc(50%-2.5rem)]">
                        <div class="mb-2 flex items-start justify-between">
                            <div>
                                <h3 class="text-charcoal text-base font-bold">{{ $umkm->business_name }}</h3>
                                <p class="mt-0.5 flex items-center gap-1 text-xs text-gray-500">
                                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ $umkm->owner_name }}
                                </p>
                            </div>
                            <a href="{{ route('umkm.recommended', $umkm->id) }}"
                                class="text-primary hover:bg-primary/10 rounded-full p-2 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                        <div class="mt-3 border-t border-gray-100 pt-3">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">Beli di sini:</p>
                            <div class="space-y-2">
                                @php
                                    $stopCategoryIds = collect($stop['categories'])
                                        ->map(function ($c) {
                                            return is_array($c) ? $c['id'] ?? $c : (is_object($c) ? $c->id ?? $c : $c);
                                        })
                                        ->toArray();
                                @endphp
                                @foreach ($umkm->activeProducts as $product)
                                    @if (in_array($product->umkm_product_category_id, $stopCategoryIds))
                                        @php $totalPrice += $product->price; @endphp
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-charcoal">{{ $product->name }}</span>
                                            <span class="text-primary font-bold">Rp
                                                {{ number_format($product->price, 0, ',', '.') }}</span>
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
    <div
        class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-100 bg-white/90 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-8px_20px_rgba(0,0,0,0.06)] backdrop-blur-md">
        <div class="mb-3 flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-500">Total Estimasi Belanja</span>
            <span class="text-primary font-display text-base font-extrabold">Rp
                {{ number_format($totalPrice, 0, ',', '.') }}</span>
        </div>
        <button
            class="bg-primary flex h-12 w-full items-center justify-center gap-2 rounded-xl font-bold text-white transition-all active:scale-[0.98]"
            onclick="startNavigation()">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
            </svg>
            Mulai Perjalanan
        </button>
    </div>
@endsection


@push('scripts')
    <script>
        (function() {
            const routeData = @json($route);
            const mapCoordinates = routeData.map(stop => {
                const umkm = stop.umkm;
                if (!umkm) return null;
                const loc = umkm.map_location || umkm.mapLocation;
                return loc ? [parseFloat(loc.latitude), parseFloat(loc.longitude)] : null;
            }).filter(coord => coord !== null);

            // Initialize Map
            let mapInstance = null;
            const mapEl = document.getElementById('map');
            if (mapEl) {
                mapInstance = L.map(mapEl, {
                    zoomControl: false,
                    attributionControl: false
                });

                // Add CartoDB Positron tiles for a clean look
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 20
                }).addTo(mapInstance);

                // Add markers
                const bounds = L.latLngBounds();

                routeData.forEach((stop, index) => {
                    const umkm = stop.umkm;
                    if (!umkm) return;
                    const loc = umkm.map_location || umkm.mapLocation;
                    if (!loc) return;

                    const lat = parseFloat(loc.latitude);
                    const lng = parseFloat(loc.longitude);
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

                    L.marker([lat, lng], {
                            icon: customIcon
                        })
                        .bindPopup(`<b>${umkm.business_name || 'UMKM'}</b>`)
                        .addTo(mapInstance);
                });

                if (mapCoordinates.length > 0) {
                    mapInstance.fitBounds(bounds, {
                        padding: [30, 30]
                    });
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
                            body: JSON.stringify({
                                coordinates: orsCoordinates
                            })
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
                                }).addTo(mapInstance);
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
            }

            function drawStraightLines() {
                if (mapInstance) {
                    L.polyline(mapCoordinates, {
                        color: '#F97316',
                        weight: 3,
                        opacity: 0.6,
                        dashArray: '5, 10'
                    }).addTo(mapInstance);
                }
            }

            function startNavigation() {
                if (navigator.vibrate) navigator.vibrate(50);

                // Extract coordinate strings: "lat,lng|lat,lng"
                const coordsStr = mapCoordinates.map(coord => coord.join(',')).join('|');

                // Redirect to explore page with action=multi_route and coordinates
                window.location.href = `/explore?action=multi_route&stops=${encodeURIComponent(coordsStr)}`;
            }

            // Expose required functions to window for inline HTML onclick attributes
            window.startNavigation = startNavigation;

            // Clean up Leaflet map instance on Livewire navigation
            document.addEventListener('livewire:navigating', function cleanup(e) {
                if (mapInstance) {
                    mapInstance.remove();
                    mapInstance = null;
                }
                delete window.startNavigation;
                document.removeEventListener('livewire:navigating', cleanup);
            });
        })();
    </script>
@endpush
