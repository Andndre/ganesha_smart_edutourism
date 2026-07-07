<!-- Info Card -->
<div class="relative z-10 -mt-4 rounded-t-3xl border-b border-gray-100 bg-white px-5 py-6 shadow-sm lg:mt-0 lg:rounded-3xl lg:border lg:border-gray-100 lg:shadow-sm lg:px-7 lg:py-7">
    <div class="mb-4 flex items-center gap-4">
        <div class="text-primary flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gray-100 shadow-inner">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
            </svg>
        </div>
        <div>
            <h1 class="text-charcoal text-xl font-bold lg:text-2xl">{{ $umkm->business_name }}</h1>
            <p class="mt-1 flex items-center gap-1 text-sm text-gray-500">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ __('Milik:') }} {{ $umkm->owner_name }}
            </p>
        </div>
    </div>

    @if ($umkm->description)
        <p class="mt-2 text-sm text-gray-600 leading-relaxed lg:text-base">{{ $umkm->description }}</p>
    @endif

    <!-- Inline CTA (desktop only) -->
    <div class="mt-6 hidden border-t border-gray-100 pt-6 lg:block">
        @if ($umkm->user && $umkm->user->phone)
            @php
                $phone = preg_replace('/[^0-9]/', '', $umkm->user->phone);
                if (str_starts_with($phone, '0')) {
                    $phone = '62' . substr($phone, 1);
                }
                $text = __('Halo, saya tertarik untuk memesan produk dari toko :store Anda.', [
                    'store' => $umkm->business_name,
                ]);
                $waUrl = 'https://wa.me/' . $phone . '?text=' . urlencode($text);
            @endphp
            <a href="{{ $waUrl }}" target="_blank"
                class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all hover:bg-[#152E1D] active:scale-[0.98]"
                onclick="if(navigator.vibrate) navigator.vibrate(50)">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                    <path d="M11.999 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.968-1.418A9.953 9.953 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a7.952 7.952 0 01-4.342-1.285l-.311-.185-3.23.923.936-3.14-.203-.323A7.953 7.953 0 014 12c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8z"/>
                </svg>
                {{ __('Hubungi Penjual') }}
            </a>
        @else
            <button disabled
                class="flex h-14 w-full cursor-not-allowed items-center justify-center rounded-2xl bg-gray-200 font-bold text-gray-400"
                title="{{ __('Penjual belum mencantumkan nomor kontak') }}">
                {{ __('Hubungi Penjual') }}
            </button>
        @endif
    </div>
</div>
