{{-- Selected Points List --}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="font-semibold text-charcoal flex items-center gap-2">
            <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            </svg>
            Urutan Kunjungan Rute
        </h2>
        <span id="points-count-badge" class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-bold text-gray-500">0 Titik</span>
    </div>

    {{-- Empty state --}}
    <div id="points-empty-state" class="rounded-xl border border-dashed border-gray-200 p-8 text-center text-xs text-gray-400">
        Belum ada titik yang dipilih. Silakan klik penanda lokasi di peta di sebelah kanan untuk menambahkan titik kunjungan.
    </div>

    {{-- Reorderable List Container --}}
    <div id="points-list-container" class="space-y-3">
        {{-- Rendered dynamically by javascript --}}
    </div>
</div>
