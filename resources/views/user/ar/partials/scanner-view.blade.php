<!-- 1. Scanner View -->
<div id="scanner-view" class="absolute inset-0 z-0 bg-black">
    <div id="reader" class="h-full w-full object-cover"></div>

    <div id="start-camera-overlay"
        class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-black/80 p-6 backdrop-blur-md transition-all">
        <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-white/10">
            <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <h3 class="mb-2 text-xl font-bold text-white">Mulai Pemindaian AR</h3>
        <p class="mb-8 max-w-xs text-center text-sm text-gray-300">Ketuk tombol di bawah untuk mengaktifkan kamera dan
            mulai memindai marker QR.</p>
        <button id="btn-start-camera"
            class="rounded-xl bg-[#1E5128] px-8 py-3.5 font-semibold text-white shadow-lg shadow-[#1E5128]/30 transition-all active:scale-95">
            Buka Kamera
        </button>
    </div>

    <!-- UI Overlay for Scanner (Reticle) -->
    <div class="pointer-events-none absolute inset-0 z-20 flex items-center justify-center">
        <div class="relative h-64 w-64 rounded-3xl border-2 border-white/50">
            <div class="absolute -left-1 -top-1 h-8 w-8 rounded-tl-3xl border-l-4 border-t-4 border-[#1E5128]"></div>
            <div class="absolute -right-1 -top-1 h-8 w-8 rounded-tr-3xl border-r-4 border-t-4 border-[#1E5128]"></div>
            <div class="absolute -bottom-1 -left-1 h-8 w-8 rounded-bl-3xl border-b-4 border-l-4 border-[#1E5128]"></div>
            <div class="absolute -bottom-1 -right-1 h-8 w-8 rounded-br-3xl border-b-4 border-r-4 border-[#1E5128]">
            </div>
        </div>
    </div>
</div>
