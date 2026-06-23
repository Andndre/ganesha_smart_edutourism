<!-- Status Badge -->
<div class="pointer-events-none absolute inset-x-0 top-6 z-50 flex justify-center">
    <div id="status-badge"
        class="rounded-full bg-black/40 px-4 py-1.5 text-xs font-bold text-white shadow-lg backdrop-blur-md transition-colors">
        {{ __('Arahkan ke Marker QR') }}
    </div>
</div>

<!-- Back Button -->
<button id="btn-back-scanner"
    class="absolute left-4 top-6 z-50 flex h-10 w-10 items-center justify-center rounded-full bg-black/40 text-white shadow-lg backdrop-blur-md transition-transform hover:bg-black/60 active:scale-95">
    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
    </svg>
</button>

<!-- Exit Confirmation Overlay -->
<div id="exit-confirm-overlay"
    class="z-100 absolute inset-0 hidden flex-col items-center justify-center bg-black/80 p-6 backdrop-blur-sm transition-all">
    <div class="w-full max-w-xs rounded-3xl bg-white p-6 shadow-2xl">
        <div class="mb-4 flex justify-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
        </div>
        <h3 class="mb-2 text-center text-lg font-bold text-gray-900">{{ __('Tutup Pemindai?') }}</h3>
        <p class="mb-6 text-center text-sm text-gray-500">{{ __('Kamera akan dimatikan dan Anda akan kembali ke halaman sebelumnya.') }}</p>
        <div class="flex flex-col gap-2">
            <button id="btn-confirm-exit"
                class="rounded-xl bg-red-500 py-3 font-semibold text-white transition-colors hover:bg-red-600 active:bg-red-700">
                {{ __('Ya, Tutup') }}
            </button>
            <button id="btn-cancel-exit"
                class="rounded-xl bg-gray-100 py-3 font-semibold text-gray-700 transition-colors hover:bg-gray-200 active:bg-gray-300">
                {{ __('Batal') }}
            </button>
        </div>
    </div>
</div>
