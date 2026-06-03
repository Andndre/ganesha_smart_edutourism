{{-- General Route Info --}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <h2 class="mb-4 font-semibold text-charcoal flex items-center gap-2">
        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Informasi Rute
    </h2>
    
    <div class="space-y-4">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Rute <span class="text-warning">*</span></label>
            <input type="text" name="name" required placeholder="Contoh: Rute Budaya & Sejarah Penglipuran"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <input type="hidden" name="difficulty" value="easy">
            <div class="flex flex-col justify-end pb-2">
                <label class="relative flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_smart_route" value="1" class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                    <span class="text-sm font-semibold text-gray-700">Smart Route (AI)</span>
                </label>
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Rute</label>
            <textarea name="description" rows="3" placeholder="Tulis deskripsi singkat mengenai rute perjalanan ini..."
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 resize-none"></textarea>
        </div>
    </div>
</div>
