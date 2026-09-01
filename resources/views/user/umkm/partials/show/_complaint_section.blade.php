<div class="rounded-3xl border border-gray-100 bg-white px-5 py-6 shadow-sm lg:px-7 lg:py-7">
    <h3 class="font-display text-lg font-bold text-charcoal mb-2">{{ __('Saran') }}</h3>
    <p class="text-sm text-gray-600 mb-4 leading-relaxed">
        {{ __('Punya kendala atau saran untuk toko ini? Sampaikan secara langsung ke pemilik toko dan pengelola desa secara tertutup.') }}
    </p>
    {{-- ponytail: aksi tersier — outline abu-abu, bukan amber. Alert Amber dicadangkan
         untuk peringatan kepadatan & rute darurat (DESIGN.md) --}}
    @auth
        <a href="{{ route('feedback', ['umkm_profile_id' => $umkm->id]) }}"
            class="inline-flex h-11 min-h-11 items-center justify-center rounded-xl border border-gray-200 bg-white px-5 text-sm font-bold text-gray-700 transition-all hover:bg-gray-50 active:scale-[0.98]"
            onclick="if(navigator.vibrate) navigator.vibrate(50)">
            {{ __('Kirim Masukan') }}
        </a>
    @else
        <a href="{{ route('login', ['redirect' => route('umkm.store', $umkm->id)]) }}"
            class="inline-flex h-11 min-h-11 items-center justify-center rounded-xl border border-gray-200 bg-white px-5 text-sm font-bold text-gray-700 transition-all hover:bg-gray-50 active:scale-[0.98]">
            {{ __('Masuk untuk Kirim Masukan') }}
        </a>
    @endauth
</div>
