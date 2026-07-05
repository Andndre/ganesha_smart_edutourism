{{-- General Route Info --}}
<div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
    <h2 class="mb-4 font-semibold text-charcoal flex items-center gap-2">
        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Informasi Rute
    </h2>
    
    <div class="space-y-4" x-data="{ locale: 'id' }">
        {{-- Locale tabs --}}
        <div class="sticky top-0 z-10 bg-white py-3 border-b border-gray-100 mb-4 flex gap-2">
            <button @click="locale = 'id'" :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all" type="button">Indonesia</button>
            <button @click="locale = 'en'" :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all" type="button">English</button>
        </div>

        <div x-show="locale === 'en'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Route Name (EN) <span class="text-warning">*</span></label>
            <input type="text" name="name[en]" required placeholder="e.g. Cultural & History Route of Penglipuran"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
        </div>
        <div x-show="locale === 'id'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Rute (ID) <span class="text-warning">*</span></label>
            <input type="text" name="name[id]" required placeholder="Contoh: Rute Budaya & Sejarah Penglipuran"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
        </div>

        <input type="hidden" name="difficulty" value="easy">

        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Profil Gamifikasi</label>
            @php($selectedGk = old('gamification_key'))
            <select name="gamification_key"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30">
                <option value="">Tidak ada (rute biasa)</option>
                <option value="heritage_quest" {{ $selectedGk === 'heritage_quest' ? 'selected' : '' }}>Heritage Quest</option>
                <option value="cultural_adventure" {{ $selectedGk === 'cultural_adventure' ? 'selected' : '' }}>Cultural Adventure</option>
                <option value="eco_quest" {{ $selectedGk === 'eco_quest' ? 'selected' : '' }}>Eco Quest</option>
            </select>
            <p class="mt-1 text-xs text-gray-400">Menentukan badge, collectible &amp; avatar. Aman diubah tanpa mengganggu nama rute.</p>
        </div>

        <div x-show="locale === 'en'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Route Description (EN)</label>
            <textarea name="description[en]" rows="3" placeholder="Write a short description of this route..."
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 resize-none"></textarea>
        </div>
        <div x-show="locale === 'id'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Rute (ID)</label>
            <textarea name="description[id]" rows="3" placeholder="Tulis deskripsi singkat mengenai rute perjalanan ini..."
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/30 resize-none"></textarea>
        </div>
    </div>
</div>
