{{-- ponytail: partial dipecah untuk keterbacaan --}}
<!-- Products Preview -->
<div class="border-y border-gray-100 bg-white px-5 py-6 lg:rounded-3xl lg:border lg:border-gray-100 lg:shadow-sm lg:px-7 lg:py-7" x-data>
    <h3 class="text-charcoal mb-3 font-bold lg:text-lg">{{ __('Produk yang Tersedia') }}</h3>
    <div class="space-y-3">
        @forelse($umkm->activeProducts as $product)
            <div @click="$dispatch('open-product-modal', {{ json_encode([
                'name' => $product->name,
                'category' => $product->category->name ?? __('Produk'),
                'price' => 'Rp ' . number_format($product->price, 0, ',', '.'),
                'image' => $product->image_path ? asset('storage/' . $product->image_path) : ($product->images[0] ?? null ? asset('storage/' . ($product->images[0])) : ''),
                'description' => $product->description ?? __('Tidak ada deskripsi.'),
            ]) }})"
                class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-100 p-3 transition-colors hover:border-primary/20 hover:bg-primary/5 active:bg-gray-50">
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-100 text-gray-400">
                    @php
                        $prodImg = $product->image_path ?: ($product->images[0] ?? null);
                    @endphp
                    @if ($prodImg)
                        <img src="{{ asset('storage/' . $prodImg) }}" alt="{{ $product->name }}"
                            class="h-full w-full object-cover">
                    @else
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-charcoal line-clamp-2 text-sm font-bold">{{ $product->name }}</h4>
                    <p class="mt-0.5 text-xs text-gray-500">{{ $product->category->name ?? __('Produk') }}</p>
                </div>
                <div class="text-primary shrink-0 text-sm font-bold">
                    Rp {{ number_format($product->price, 0, ',', '.') }}
                </div>
            </div>
        @empty
            <p class="py-4 text-center text-sm text-gray-500">{{ __('UMKM ini tidak memiliki produk aktif saat ini.') }}</p>
        @endforelse
    </div>
</div>
