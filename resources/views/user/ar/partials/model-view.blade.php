<!-- 2. 3D Model View (Hidden Initially) -->
<div id="model-view" class="absolute inset-0 z-10 hidden bg-[#1a1a1a]">

    <!-- iOS In-App Browser Warning -->
    <div id="iab-warning" class="absolute inset-0 z-60 hidden flex-col items-center justify-center bg-black/90 p-6 text-center text-white backdrop-blur-sm">
        <div class="mb-6 rounded-full bg-yellow-500/20 p-4">
            <svg class="h-12 w-12 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <h3 class="mb-3 text-2xl font-bold text-yellow-400">{{ __('Browser Tidak Didukung') }}</h3>
        <p class="mb-8 text-base leading-relaxed text-gray-300">
            {{ __('Untuk menggunakan fitur Kamera AR, ketuk ikon 3-titik di pojok layar dan pilih ') }} <br>
            <strong class="text-white">"Buka di Browser Sistem / Safari"</strong>.
        </p>
        <button onclick="document.getElementById('iab-warning').classList.add('hidden'); document.getElementById('iab-warning').classList.remove('flex');" class="rounded-full border border-gray-600 px-6 py-2 text-sm font-medium text-gray-400 transition-colors hover:bg-gray-800 hover:text-white">
            {{ __('Tetap Lanjutkan (Tanpa AR)') }}
        </button>
    </div>

    <model-viewer id="ar-model-viewer" src="" alt="3D Model" camera-controls auto-rotate ar ar-scale="auto"
        ar-placement="floor" bounds="tight" ar-modes="scene-viewer quick-look webxr" quick-look-browsers="safari chrome"
        environment-image="neutral" exposure="1" shadow-intensity="1" class="h-full w-full outline-none">

        <!-- Error State for AR -->
        <div id="ar-error"
            class="absolute inset-0 z-50 hidden items-center justify-center bg-black/80 p-6 text-center text-white">
            <div>
                <svg class="mx-auto mb-4 h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <h3 class="mb-2 text-xl font-bold">{{ __('AR Tidak Didukung') }}</h3>
                <p class="mb-6 text-sm text-gray-400">{{ __('Perangkat Anda mungkin tidak mendukung teknologi WebXR atau ARCore.') }}</p>
                <button onclick="const el=document.getElementById('ar-error');el.classList.add('hidden');el.classList.remove('flex')"
                    class="rounded-lg bg-gray-800 px-6 py-2 font-medium text-white">{{ __('Tutup') }}</button>
            </div>
        </div>

        <button slot="ar-button"
            class="absolute bottom-32 left-1/2 flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-6 py-3 font-bold text-gray-900 shadow-lg">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            {{ __('Tampilkan di Ruang Nyata') }}
        </button>
    </model-viewer>

    <!-- Audio Narasi -->
    <audio id="ar-audio" preload="none" class="hidden"></audio>

    <!-- Audio Control Button -->
    <button id="btn-audio-toggle"
        class="absolute bottom-36 left-4 z-50 hidden h-10 w-10 items-center justify-center rounded-full bg-black/40 text-white shadow-lg backdrop-blur-md transition-all hover:bg-black/60 active:scale-95">
        <svg id="audio-icon-play" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
        </svg>
        <svg id="audio-icon-pause" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6" />
        </svg>
    </button>

    <!-- Bottom Sheet for Model Description -->
    <div class="pointer-events-none absolute inset-0 z-30 flex flex-col justify-end overflow-hidden">
        <!-- Backdrop -->
        <div id="sheet-backdrop" class="pointer-events-auto absolute inset-0 bg-black/40 opacity-0 transition-opacity duration-300" style="display: none;"></div>

        <!-- Sheet Container -->
        <div id="desc-bottom-sheet" class="pointer-events-auto relative z-10 flex h-[75vh] w-full translate-y-[calc(100%-100px)] flex-col rounded-t-4xl bg-white shadow-[0_-4px_25px_rgba(0,0,0,0.15)] transition-transform duration-300 ease-in-out">
            <!-- Header (Always visible, height: 100px) -->
            <div id="sheet-header" class="flex h-[100px] shrink-0 cursor-pointer flex-col items-center rounded-t-4xl px-6 pt-3 transition-colors active:bg-gray-50">
                <div class="flex w-full justify-center">
                    <svg id="sheet-arrow" class="mb-2 h-6 w-6 text-gray-400 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                    </svg>
                </div>
                <div class="flex w-full flex-col items-center justify-center">
                    <h2 id="model-title" class="truncate text-xl font-bold text-gray-900">{{ __('Memuat...') }}</h2>
                </div>
            </div>

            <!-- Expanded Content (Scrollable) -->
            <div class="flex-1 overflow-y-auto px-6 pb-12 pt-4">
                <div id="model-desc-full" class="prose prose-sm prose-gray max-w-none text-gray-700 leading-relaxed"></div>
            </div>
        </div>
    </div>

    <!-- Scan Again Button -->
    <button id="btn-scan-again"
        class="absolute right-4 top-20 z-50 flex h-10 items-center justify-center gap-2 rounded-full bg-[#1E5128] px-4 text-sm font-bold text-white shadow-lg transition-transform hover:bg-[#152E1D] active:scale-95">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        {{ __('Pindai Ulang') }}
    </button>
</div>
