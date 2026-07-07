{{-- ponytail: partial dipecah untuk keterbacaan --}}
@if ($umkmList->isNotEmpty())
    <div class="grid grid-cols-2 gap-4 md:grid-cols-3 md:gap-6 lg:grid-cols-4">
        @foreach ($umkmList as $umkm)
            <a href="{{ route('umkm.store', $umkm->id) }}"
                class="relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all hover:border-gray-200 hover:shadow-md active:scale-[0.98]">
                <div class="relative aspect-video bg-gray-100">
                    @if (optional($umkm)->image_path)
                        <img src="{{ asset('storage/' . $umkm->image_path) }}" alt="{{ $umkm->business_name }}"
                            class="h-full w-full object-cover">
                    @else
                        <div class="text-primary absolute inset-0 flex items-center justify-center opacity-50">
                            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                            </svg>
                        </div>
                    @endif
                </div>

                <div class="flex-1 p-3">
                    <h3 class="text-charcoal line-clamp-1 text-sm font-bold">{{ $umkm->business_name }}</h3>
                    @php $range = $umkm->price_range; @endphp
                    @if ($range)
                        <p class="text-primary mt-1 text-xs font-semibold">
                            @if ($range['min'] === $range['max'])
                                Rp {{ number_format($range['min'], 0, ',', '.') }}
                            @else
                                Rp {{ number_format($range['min'], 0, ',', '.') }} –
                                {{ number_format($range['max'], 0, ',', '.') }}
                            @endif
                        </p>
                    @elseif ($umkm->activeProducts->isNotEmpty())
                        <p class="mt-1 text-xs text-gray-400">
                            {{ $umkm->activeProducts->first()->category?->name ?? '' }}</p>
                    @else
                        <p class="mt-1 text-xs text-amber-500">{{ __('Tidak ada produk') }}</p>
                    @endif
                </div>
            </a>
        @endforeach
    </div>

    {{-- Load More --}}
    @if ($umkmList->hasMorePages())
        <div class="mt-8 text-center" x-data="{ page: {{ $umkmList->currentPage() }} }">
            <a href="{{ $umkmList->nextPageUrl() }}"
                class="inline-block rounded-xl border border-gray-200 bg-white px-8 py-3 text-sm font-bold text-gray-700 shadow-sm transition-all hover:border-gray-300 active:scale-[0.98]">
                {{ __('Muat Lebih Banyak') }}
            </a>
        </div>
    @endif
@else
    {{-- Empty State --}}
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-50 text-gray-400">
            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
            </svg>
        </div>
        <h3 class="text-charcoal text-base font-bold">{{ __('Belum ada UMKM terdaftar') }}</h3>
        <p class="mt-1 text-xs text-gray-500">{{ __('Belum ada UMKM yang terdaftar di kawasan ini.') }}</p>
    </div>
@endif
