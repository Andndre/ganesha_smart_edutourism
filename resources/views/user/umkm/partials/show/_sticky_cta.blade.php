<!-- Sticky Bottom CTA (mobile & tablet only) -->
<div class="fixed bottom-(--route-banner-h,0px) inset-x-0 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] bg-white border-t border-gray-100 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] lg:hidden">
    <div class="mx-auto w-full max-w-2xl">
        <div class="flex items-center gap-3">
            @if($umkm->user && $umkm->user->phone)
                @php
                    $phone = preg_replace('/[^0-9]/', '', $umkm->user->phone);
                    if (str_starts_with($phone, '0')) {
                        $phone = '62' . substr($phone, 1);
                    }
                    $text = __('Halo, saya tertarik untuk memesan produk dari toko :store Anda.', [
                        'store' => $umkm->business_name
                    ]);
                    $waUrl = 'https://wa.me/' . $phone . '?text=' . urlencode($text);
                @endphp
                <a href="{{ $waUrl }}" target="_blank"
                    class="flex-1 bg-primary text-white font-bold h-12 rounded-xl flex items-center justify-center gap-2 active:scale-[0.98] transition-all"
                    onclick="if(navigator.vibrate) navigator.vibrate(50)">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        <path d="M11.999 2C6.477 2 2 6.477 2 12c0 1.89.525 3.66 1.438 5.168L2 22l4.968-1.418A9.953 9.953 0 0012 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18a7.952 7.952 0 01-4.342-1.285l-.311-.185-3.23.923.936-3.14-.203-.323A7.953 7.953 0 014 12c0-4.418 3.582-8 8-8s8 3.582 8 8-3.582 8-8 8z"/>
                    </svg>
                    {{ __('Hubungi Penjual') }}
                </a>
            @else
                <button disabled
                    class="flex-1 bg-gray-300 text-white font-bold h-12 rounded-xl cursor-not-allowed"
                    title="{{ __('Penjual belum mencantumkan nomor kontak') }}">
                    {{ __('Hubungi Penjual') }}
                </button>
            @endif
        </div>
    </div>
</div>
