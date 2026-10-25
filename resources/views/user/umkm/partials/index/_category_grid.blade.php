{{-- ponytail: partial dipecah untuk keterbacaan --}}
@php
    $checkedCategoryIds = (array) old('category_ids', []);
@endphp
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
    @foreach ($categories as $category)
        @php
            $isChecked = in_array($category['id'], $checkedCategoryIds);
            $isInitiallyHidden = $loop->index >= 12 && !$isChecked;
        @endphp
        <div id="card-cat-{{ $category['id'] }}"
            class="category-card relative flex h-full cursor-pointer flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all hover:border-gray-200 hover:shadow-md {{ $isInitiallyHidden ? 'hidden' : '' }}"
            data-name="{{ strtolower($category['name']) }}"
            data-description="{{ strtolower($category['description'] ?? '') }}"
            onclick="openCategoryModal({{ json_encode($category) }}, event)">
            <div class="relative aspect-video bg-gray-100" x-data="{ loaded: false, failed: false }">
                @if ($category['image_path'])
                    <img src="{{ asset('storage/' . $category['image_path']) }}" alt="{{ $category['name'] }}"
                        @if ($loop->index < 4)
                            loading="eager" fetchpriority="high"
                        @else
                            loading="lazy" decoding="async"
                        @endif
                        x-on:load="loaded = true" x-on:error="failed = true; loaded = true"
                        class="h-full w-full object-cover">
                    <div x-show="!loaded" class="absolute inset-0 animate-pulse bg-gray-200"></div>
                    <div x-show="failed" x-cloak
                        class="text-primary absolute inset-0 flex items-center justify-center bg-gray-100 text-xs">
                        {{ __('Gambar tidak tersedia') }}
                    </div>
                @else
                    <div class="text-primary absolute inset-0 flex items-center justify-center opacity-50">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                @endif

                <!-- Checkbox Container at Top-Right -->
                {{-- ponytail: label padding, bukan checkbox lebih besar — 22px visual, 46px tap target (min 44px) --}}
                <div class="absolute right-0 top-0 z-10" onclick="event.stopPropagation()">
                    <label class="flex cursor-pointer items-center justify-center p-3"
                        aria-label="{{ __('Pilih') }} {{ $category['name'] }}">
                        <input type="checkbox" name="category_ids[]" value="{{ $category['id'] }}"
                            id="checkbox-cat-{{ $category['id'] }}"
                            class="w-5.5 h-5.5 text-primary focus:ring-primary accent-primary cursor-pointer rounded-md border-gray-300 transition-all focus:ring-offset-0"
                            @checked($isChecked)
                            onchange="updateCardHighlight({{ $category['id'] }})">
                    </label>
                </div>
            </div>
            <div class="flex flex-1 flex-col justify-between p-3">
                <div>
                    <h3 class="text-charcoal text-sm font-bold">{{ $category['name'] }}</h3>
                    @if ($category['description'])
                        <p class="mt-1 line-clamp-2 text-[13px] text-gray-600">{{ $category['description'] }}</p>
                    @endif
                </div>
                @if (!empty($category['price']) && (float) $category['price'] > 0)
                    <div class="mt-2.5 flex items-baseline gap-1 border-t border-gray-50 pt-2">
                        <span class="text-[11px] font-medium text-gray-500">{{ __('Mulai') }}</span>
                        <span class="text-primary text-sm font-bold">
                            Rp {{ number_format((float) $category['price'], 0, ',', '.') }}
                        </span>
                        @if (!empty($category['unit']))
                            <span class="text-[11px] text-gray-500">/ {{ $category['unit'] }}</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div id="category-load-more" class="mt-8 {{ count($categories) > 12 ? '' : 'hidden' }} text-center">
    <button type="button" id="category-load-more-button"
        class="min-h-11 rounded-xl border border-gray-200 bg-white px-8 py-3 text-sm font-bold text-gray-700 shadow-sm transition-all hover:border-gray-300 active:scale-[0.98]">
        {{ __('Muat Lebih Banyak') }}
    </button>
</div>

<!-- Empty State for Search -->
<div id="empty-state" class="hidden flex-col items-center justify-center py-12 text-center">
    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 text-gray-500">
        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
    <h3 class="text-charcoal text-base font-bold">{{ __('Kategori Tidak Ditemukan') }}</h3>
    <p class="mt-1 text-xs text-gray-500">{{ __('Coba gunakan kata kunci pencarian yang lain.') }}</p>
</div>
