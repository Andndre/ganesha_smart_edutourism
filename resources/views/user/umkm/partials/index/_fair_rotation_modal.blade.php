<x-modal name="fair-rotation" maxWidth="lg">
    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#eaf3ec] text-primary"><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h13m0 0-3-3m3 3-3 3m6 7H7m0 0 3-3m-3 3 3 3"/></svg></div>
    <p class="text-xs font-bold uppercase tracking-widest text-amber-800">{{ __('Rekomendasi yang adil') }}</p>
    <h2 class="mt-2 font-display text-2xl font-bold text-charcoal">{{ __('Setiap UMKM mendapat kesempatan') }}</h2>
    <p class="mt-3 text-sm leading-relaxed text-gray-600">{{ __('Smart Route membantu Anda menemukan produk pilihan sekaligus membagi kunjungan secara adil kepada usaha warga yang aktif.') }}</p>
    <div class="mt-6 space-y-3">
        <div class="flex gap-3 rounded-xl bg-surface p-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">1</span><p class="text-sm leading-relaxed text-gray-700">{{ __('Hanya UMKM aktif dengan produk tersedia dan stok mencukupi yang dipertimbangkan.') }}</p></div>
        <div class="flex gap-3 rounded-xl bg-surface p-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">2</span><p class="text-sm leading-relaxed text-gray-700">{{ __('Jika satu UMKM memiliki semua kategori pilihan, yang paling jarang direkomendasikan mendapat giliran lebih dulu.') }}</p></div>
        <div class="flex gap-3 rounded-xl bg-surface p-4"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">3</span><p class="text-sm leading-relaxed text-gray-700">{{ __('Jika perlu beberapa pemberhentian, rute mempertimbangkan kelengkapan produk, jarak, dan jumlah rekomendasi sebelumnya.') }}</p></div>
    </div>
    <button type="button" @click="isOpen = false" class="mt-6 min-h-11 w-full rounded-full bg-primary px-5 text-sm font-bold text-white">{{ __('Mengerti, mulai jelajah') }}</button>
</x-modal>
