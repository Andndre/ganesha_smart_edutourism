{{-- ponytail: partial dipecah untuk keterbacaan --}}
        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="relative z-20 mb-4 rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-sm"
                role="alert">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Session Error -->
        @if (session('error'))
            {{-- ponytail: id dipakai _scripts untuk auto-hapus begitu user pilih kategori lain,
                 supaya banner tidak basi. Dismiss + 1 jalan keluar, bukan buntu. --}}
            <div id="recommend-error"
                class="relative z-20 mb-4 rounded-xl border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow-sm"
                role="alert">
                <div class="flex items-start gap-2">
                    <span class="block flex-1 font-medium">{{ session('error') }}</span>
                    <button type="button" onclick="this.closest('#recommend-error').remove()"
                        class="-m-2 shrink-0 p-2 text-red-700/70 transition-colors hover:text-red-700"
                        aria-label="{{ __('Tutup') }}">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <button type="button"
                    onclick="window.dispatchEvent(new CustomEvent('switch-umkm-tab', { detail: 'direktori' })); this.closest('#recommend-error').remove()"
                    class="mt-2 text-sm font-bold underline underline-offset-2">
                    {{ __('Lihat Direktori UMKM') }}
                </button>
            </div>
        @endif

        <!-- Missing Categories Warning (if partial multi-stop) -->
        @if (session('missing_categories'))
            <div class="relative z-20 mb-4 rounded-xl border border-yellow-400 bg-yellow-50 px-4 py-3 text-yellow-800 shadow-sm"
                role="alert">
                <span class="block font-medium sm:inline">{{ __('Beberapa pesanan Anda tidak tersedia di UMKM manapun:') }}</span>
                <ul class="mt-1 list-disc pl-5 text-sm">
                    @foreach (session('missing_categories') as $missingName)
                        <li>{{ $missingName }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
