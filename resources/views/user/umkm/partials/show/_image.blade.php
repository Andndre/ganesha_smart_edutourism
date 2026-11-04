<!-- Image -->
<div class="w-full aspect-video bg-gray-100 relative lg:rounded-3xl lg:border lg:border-gray-100 lg:shadow-sm lg:overflow-hidden">
    @php
        $firstProduct = $umkm->activeProducts->first();
        $imagePath = $umkm->image_path ?? ($firstProduct ? ($firstProduct->image_path ?? $firstProduct->images[0] ?? null) : null) ?? 'images/packages/kuliner-paon.webp';
        $imageUrl = \Illuminate\Support\Str::startsWith($imagePath, 'images/') ? asset($imagePath) : asset('storage/' . $imagePath);
    @endphp
    <img src="{{ $imageUrl }}" alt="{{ $umkm->business_name }}"
        class="h-full w-full object-cover"
        onerror="this.src='{{ asset('images/packages/kuliner-paon.webp') }}'">
</div>
