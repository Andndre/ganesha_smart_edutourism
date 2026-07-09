<div class="rounded-3xl border border-gray-100 bg-white px-5 py-6 shadow-sm lg:px-7 lg:py-7">
    <h3 class="font-display text-lg font-bold text-charcoal mb-2">{{ __('Saran') }}</h3>
    <p class="text-sm text-gray-600 mb-4 leading-relaxed">
        {{ __('Punya kendala atau saran untuk toko ini? Sampaikan secara langsung ke pemilik toko dan pengelola desa secara tertutup.') }}
    </p>
    @auth
        <a href="{{ route('feedback', ['umkm_profile_id' => $umkm->id]) }}"
            class="inline-flex h-11 min-h-11 items-center justify-center rounded-xl bg-amber-600 px-5 text-sm font-bold text-white shadow-sm transition-all hover:bg-amber-700 active:scale-[0.98]"
            onclick="if(navigator.vibrate) navigator.vibrate(50)">
            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ __('Kirim Masukan') }}
        </a>
    @else
        <a href="{{ route('login', ['redirect' => route('umkm.store', $umkm->id)]) }}"
            class="inline-flex h-11 min-h-11 items-center justify-center rounded-xl bg-gray-150 px-5 text-sm font-bold text-gray-500 hover:bg-gray-200">
            {{ __('Masuk untuk Kirim Masukan') }}
        </a>
    @endauth
</div>
