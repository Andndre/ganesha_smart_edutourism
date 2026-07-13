{{-- ponytail: partial dipecah untuk keterbacaan --}}
    <!-- Multi-Stop Recommendation Modal -->
    @if (session('show_multi_stop_modal'))
        <x-modal name="multi-stop" maxWidth="sm" :defaultOpen="true">
            <div class="text-center">
                <div
                    class="bg-primary/10 text-primary mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                </div>
                <h3 class="text-charcoal mb-2 text-xl font-bold">{{ __('Satu Tempat Tidak Cukup!') }}</h3>
                <p class="mb-6 text-sm text-gray-500">{{ __('Tapi jangan khawatir, kami telah menyusun') }} <span
                        class="text-charcoal font-bold">{{ __('rute terdekat') }}</span> {{ __('agar Anda bisa mendapatkan semua barang pilihan Anda dari beberapa UMKM sekaligus.') }}</p>
                <div class="space-y-3">
                    <a href="{{ route('umkm.multi_recommended') }}"
                        class="bg-primary block w-full rounded-xl py-3.5 font-bold text-white shadow-lg transition-transform active:scale-[0.98]">
                        {{ __('Lihat Rute Belanja') }}
                    </a>
                    <button @click="isOpen = false"
                        class="block w-full rounded-xl bg-gray-100 py-3.5 font-bold text-gray-600 transition-transform active:scale-[0.98]">
                        {{ __('Batal') }}
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
