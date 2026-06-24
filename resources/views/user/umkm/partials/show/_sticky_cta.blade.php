{{-- ponytail: partial dipecah untuk keterbacaan --}}
<!-- Sticky Bottom CTA -->
<div class="fixed bottom-0 inset-x-0 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] bg-white border-t border-gray-100 z-30 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
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
                class="flex-1 bg-primary text-white font-bold h-12 rounded-xl flex items-center justify-center active:scale-[0.98] transition-all"
                onclick="if(navigator.vibrate) navigator.vibrate(50)">
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
