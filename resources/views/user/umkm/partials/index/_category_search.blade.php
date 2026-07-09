{{-- Category grid local filter --}}
<div class="relative mb-6">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>

    <input type="text" id="category-search-input"
        class="focus:border-primary focus:ring-primary/20 w-full rounded-xl border border-gray-200 bg-white px-5 py-3.5 pl-12 text-sm shadow-sm transition-all placeholder:text-gray-400 focus:outline-none focus:ring-2"
        placeholder="{{ __('Cari kategori produk...') }}"
        autocomplete="off">

    <button type="button" id="category-clear-search-btn"
        class="absolute inset-y-0 right-0 hidden items-center pr-4 text-gray-400 hover:text-gray-600 focus:outline-none"
        aria-label="{{ __('Hapus pencarian') }}">
        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>
