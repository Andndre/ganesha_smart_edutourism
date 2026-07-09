{{-- UMKM directory server-side filter --}}
<form method="GET" action="{{ route('umkm') }}" class="relative mb-6">
    <input type="hidden" name="tab" value="direktori">

    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>

    <input type="search" name="q" value="{{ $q ?? '' }}"
        class="focus:border-primary focus:ring-primary/20 w-full rounded-xl border border-gray-200 bg-white px-5 py-3.5 pl-12 pr-24 text-sm shadow-sm transition-all placeholder:text-gray-400 focus:outline-none focus:ring-2"
        placeholder="{{ __('Cari UMKM atau produk...') }}"
        autocomplete="off">

    @if (!empty($q))
        <a href="{{ route('umkm', ['tab' => 'direktori']) }}"
            class="absolute inset-y-0 right-12 z-10 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
            aria-label="{{ __('Hapus pencarian') }}">
            <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </a>
    @endif

    <button type="submit"
        class="bg-primary hover:bg-primary-600 absolute inset-y-1 right-1 rounded-lg px-3 text-xs font-bold text-white shadow-sm transition-colors focus:outline-none">
        {{ __('Cari') }}
    </button>
</form>
