{{-- ponytail: partial dipecah untuk keterbacaan --}}
<!-- Product Detail Modal -->
<div x-data="{ selectedProduct: null }" @open-product-modal.window="selectedProduct = $event.detail">
    <x-modal name="product-modal" maxWidth="md">
        <!-- Image Header -->
        <div class="relative mb-6 h-48 w-full overflow-hidden rounded-2xl bg-gray-100">
            <template x-if="selectedProduct?.image">
                <img :src="selectedProduct.image" class="h-full w-full object-cover" alt="{{ __('Product Image') }}">
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
                class="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/40 text-white backdrop-blur-sm transition-all hover:bg-black/60 active:scale-95 md:hidden"
                title="{{ __('Tutup') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
