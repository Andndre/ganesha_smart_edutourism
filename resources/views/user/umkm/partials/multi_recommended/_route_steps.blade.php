@php
    use App\Models\MapLocation;
    use App\Models\UmkmProduct;
    use App\Models\UmkmProfile;
@endphp
{{-- ponytail: partial dipecah untuk keterbacaan --}}
<!-- Itinerary / Route Steps -->
<div
    class="before:bg-linear-to-b relative space-y-4 before:absolute before:inset-0 before:ml-5 before:h-full before:w-0.5 before:-translate-x-px before:from-transparent before:via-slate-200 before:to-transparent md:before:mx-auto md:before:translate-x-0">
    @foreach ($route as $index => $stop)
        @php
            $umkm = $stop['umkm'];
            if (is_object($umkm) && !($umkm instanceof UmkmProfile)) {
                $umkm = json_decode(json_encode($umkm), true);
            }
            if (\is_array($umkm)) {
                $umkmModel = new UmkmProfile();
                $umkmModel->exists = true;
                $umkmModel->forceFill($umkm);
                if (isset($umkm['products'])) {
                    $products = collect($umkm['products'])->map(function ($p) {
                        return new UmkmProduct()->forceFill($p);
                    });
                    $umkmModel->setRelation('products', $products);
                    $umkmModel->setRelation('activeProducts', $products);
                }
                if (isset($umkm['map_location'])) {
                    $loc = new MapLocation()->forceFill($umkm['map_location']);
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div class="mt-3 border-t border-gray-100 pt-3">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        {{ __('Beli di sini:') }}</p>
                    <div class="space-y-2">
                        @php
                            $stopCategoryIds = collect($stop['categories'])
                                ->map(function ($c) {
                                    return \is_array($c) ? $c['id'] ?? $c : (is_object($c) ? $c->id ?? $c : $c);
                                })
                                ->toArray();
                        @endphp
                        @foreach ($umkm->activeProducts as $product)
                            @if (in_array($product->umkm_product_category_id, $stopCategoryIds))
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
