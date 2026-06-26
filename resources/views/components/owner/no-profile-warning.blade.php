@props(['message'])
<div class="rounded-2xl border border-warning/20 bg-warning/5 p-6 shadow-sm max-w-3xl">
    <div class="flex items-start gap-4">
        <div class="rounded-xl bg-warning/10 p-3 text-warning">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <div>
            <h3 class="font-display text-lg font-bold text-warning-800">Profil Toko Belum Dibuat</h3>
            <p class="mt-1 text-sm text-warning-700">{{ $message }}</p>
            <div class="mt-4">
                <a href="{{ route('owner.profile') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-warning px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-warning/20 transition-all hover:bg-warning-600 active:scale-[0.98]">
                    Buat Profil Toko
                </a>
            </div>
        </div>
    </div>
</div>
