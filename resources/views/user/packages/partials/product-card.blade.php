{{-- Card for a tour package / entrance ticket item (cached array, use ['key'] access) --}}
<a href="{{ route('tour-package', $item['id']) }}"
    class="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all hover:-translate-y-1 hover:border-gray-200 hover:shadow-lg active:scale-[0.98]">
    <div class="relative aspect-video overflow-hidden bg-gray-200">
        @if ($item['images'] && count($item['images']) > 0)
            <img src="{{ Storage::url($item['images'][0]) }}" alt="{{ $item['name'] }}"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
        <div
            class="text-primary absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold shadow-sm backdrop-blur">
            {{ $badge }}
        </div>
    </div>
    <div class="flex flex-1 flex-col p-4">
        <h3 class="text-charcoal mb-1 text-lg font-bold">{{ $item['name'] }}</h3>
        <p class="mb-3 line-clamp-2 text-sm text-gray-500">{{ $item['description'] }}</p>

        <div class="mt-auto flex flex-wrap items-center justify-between gap-2 border-t border-gray-50 pt-4">
            <div class="flex items-center gap-4 text-xs font-semibold text-gray-500">
                @if (($item['type'] ?? 'package') !== 'ticket')
                    <div class="flex items-center gap-1.5">
                        <svg class="text-primary h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $item['duration_hours'] }} {{ __('Jam') }}
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="text-primary h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('Min. :count Orang', ['count' => $item['min_capacity']]) }}
                    </div>
                @else
                    <div class="flex items-center gap-1.5">
                        <svg class="text-primary h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        {{ __('Per orang') }}
                    </div>
                @endif
            </div>
            <div class="text-primary text-lg font-bold">Rp {{ number_format($item['price'], 0, ',', '.') }}
            </div>
        </div>
    </div>
</a>
