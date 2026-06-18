<!-- 2. 3D Model View (Hidden Initially) -->
<div id="model-view" class="absolute inset-0 z-10 hidden bg-[#1a1a1a]">
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
                <h3 class="mb-2 text-xl font-bold">AR Tidak Didukung</h3>
                <p class="mb-6 text-sm text-gray-400">Perangkat Anda mungkin tidak mendukung teknologi WebXR atau
                    ARCore.</p>
                <button onclick="const el=document.getElementById('ar-error');el.classList.add('hidden');el.classList.remove('flex')"
                    class="rounded-lg bg-gray-800 px-6 py-2 font-medium text-white">Tutup</button>
            </div>
        </div>

        <button slot="ar-button"
            class="absolute bottom-8 left-1/2 flex -translate-x-1/2 items-center gap-2 rounded-full bg-white px-6 py-3 font-bold text-gray-900 shadow-lg">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
            Tampilkan di Ruang Nyata
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

    <!-- UI Overlay for Model View -->
    <div
        class="bg-linear-to-t pointer-events-none absolute bottom-0 left-0 right-0 z-20 from-black/90 via-black/50 to-transparent px-5 pb-8 pt-12 text-white">
        <h2 id="model-title" class="mb-2 text-2xl font-bold drop-shadow-md">Memuat...</h2>

        <!-- Description with expand/collapse -->
        <div id="model-desc-wrapper" class="pointer-events-auto">
            <div id="model-desc-short" class="line-clamp-2 text-sm leading-relaxed text-gray-200 drop-shadow"></div>
            <div id="model-desc-full" class="hidden text-sm leading-relaxed text-gray-200 drop-shadow"></div>
            <button id="btn-desc-toggle"
                class="mt-1.5 hidden text-xs font-semibold text-[#E28F1B] underline underline-offset-2 active:opacity-70">
                Baca selengkapnya
            </button>
        </div>
    </div>

    <!-- Scan Again Button -->
    <button id="btn-scan-again"
        class="absolute right-4 top-12 z-50 flex h-10 items-center justify-center gap-2 rounded-full bg-[#1E5128] px-4 text-sm font-bold text-white shadow-lg transition-transform hover:bg-[#152E1D] active:scale-95">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Pindai Ulang
    </button>
</div>
