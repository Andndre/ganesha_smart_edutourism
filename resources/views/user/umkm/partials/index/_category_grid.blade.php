{{-- ponytail: partial dipecah untuk keterbacaan --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                @foreach ($categories as $category)
                    <div id="card-cat-{{ $category['id'] }}"
                        class="category-card relative flex h-full cursor-pointer flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all hover:border-gray-200 hover:shadow-md"
                        data-name="{{ strtolower($category['name']) }}"
                        data-description="{{ strtolower($category['description'] ?? '') }}"
                        onclick="openCategoryModal({{ json_encode($category) }}, event)">
                        <div class="relative aspect-square bg-gray-100">
                            @if ($category['image_path'])
                                <img src="{{ asset('storage/' . $category['image_path']) }}" alt="{{ $category['name'] }}"
                                    class="h-full w-full object-cover">
                            @else
                                <div class="text-primary absolute inset-0 flex items-center justify-center opacity-50">
                                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                </div>
                            @endif

                            <!-- Checkbox Container at Top-Right -->
                            <div class="absolute right-2 top-2 z-10" onclick="event.stopPropagation()">
                                <label class="flex cursor-pointer items-center justify-center">
                                    <input type="checkbox" name="category_ids[]" value="{{ $category['id'] }}"
                                        id="checkbox-cat-{{ $category['id'] }}"
                                        class="w-5.5 h-5.5 text-primary focus:ring-primary accent-primary cursor-pointer rounded-full border-gray-300 transition-all focus:ring-offset-0"
                                        onchange="updateCardHighlight({{ $category['id'] }})">
                                </label>
                            </div>
                        </div>
                        <div class="flex-1 p-3">
                            <h3 class="text-charcoal text-sm font-bold">{{ $category['name'] }}</h3>
                            @if ($category['description'])
                                <p class="mt-1 line-clamp-2 text-xs text-gray-500">{{ $category['description'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty State for Search -->
            <div id="empty-state" class="hidden flex-col items-center justify-center py-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 text-gray-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-charcoal text-base font-bold">Kategori Tidak Ditemukan</h3>
                <p class="mt-1 text-xs text-gray-500">Coba gunakan kata kunci pencarian yang lain.</p>
            </div>
