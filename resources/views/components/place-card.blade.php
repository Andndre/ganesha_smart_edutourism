@props(['item', 'favorited' => true])

<div class="favorite-card group overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md active:scale-[0.98]">
    <a href="{{ route('cultural-object', $item->slug) }}" class="block">
        <div class="relative h-48 overflow-hidden bg-gray-200">
            @if (!empty($item->historical_images))
                <img src="{{ asset('storage/' . $item->historical_images[0]) }}" alt="{{ $item->name }}"
                    class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
            @else
                <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </div>
    </a>
    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <a href="{{ route('cultural-object', $item->slug) }}">
                    <h3 class="text-charcoal text-base font-bold leading-tight">{{ $item->name }}</h3>
                </a>
                <span class="mt-1.5 inline-block rounded-md bg-green-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-green-700">
                    {{ $item->category }}
                </span>
            </div>
            <button onclick="toggleFavorite(@js(get_class($item)), {{ $item->id }}, this)"
                class="tap-target flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-50 transition-colors active:bg-gray-100"
                aria-label="{{ $favorited ? __('Hapus dari favorit') : __('Tambah ke favorit') }}">
                <svg class="h-5 w-5 {{ $favorited ? 'text-yellow-400 fill-current' : 'text-gray-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
            </button>
        </div>
        @if ($item->short_description)
            <p class="mt-2 text-xs leading-relaxed text-gray-500 line-clamp-2">{{ $item->short_description }}</p>
        @endif
    </div>
</div>
