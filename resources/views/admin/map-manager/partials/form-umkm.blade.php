{{-- FORM 2: UMKM Profile --}}
<form id="form-umkm" action="{{ route('admin.umkm.profile.store') }}" method="POST"
    class="hidden space-y-4">
    @csrf
    <div id="method-umkm"></div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Toko / Warung UMKM <span
                class="text-warning">*</span></label>
        <input type="text" name="business_name" required placeholder="Contoh: Warung Dedari"
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Pilih Akun Pemilik <span class="text-warning">*</span></label>
        <div class="relative">
            <input type="hidden" name="user_id" id="umkm-owner-user-id">
            <input type="text" id="umkm-owner-search" placeholder="Cari nama atau email pemilik..." 
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 pr-10 text-sm focus:border-primary focus:outline-none"
                onfocus="showOwnerDropdown()" oninput="filterOwners(this.value)" autocomplete="off">
            <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
            
            {{-- Dropdown options --}}
            <div id="umkm-owner-dropdown" class="absolute left-0 right-0 mt-1 max-h-48 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-lg hidden z-50">
                @forelse ($owners as $owner)
                    <div class="owner-option px-4 py-2.5 text-sm text-charcoal hover:bg-primary hover:text-white cursor-pointer transition-colors" 
                        data-id="{{ $owner->id }}" data-name="{{ $owner->name }}" data-email="{{ $owner->email }}" onclick="selectOwner({{ $owner->id }}, '{{ $owner->name }}')">
                        <div class="font-semibold">{{ $owner->name }}</div>
                        <div class="text-[10px] opacity-75 font-mono">{{ $owner->email }}</div>
                    </div>
                @empty
                    <div class="px-4 py-3 text-sm text-gray-400 italic">Belum ada akun pemilik UMKM. Buat terlebih dahulu di menu Pemilik UMKM.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Pemilik Toko <span class="text-warning">*</span></label>
        <input type="text" name="owner_name" id="umkm-owner-name" required readonly placeholder="Akan terisi otomatis saat memilih pemilik..."
            class="w-full rounded-xl bg-gray-50 border border-gray-200 px-4 py-2.5 text-sm text-gray-500 focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Kategori UMKM <span
                class="text-warning">*</span></label>
        <select name="category" required
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            <option value="culinary">Kuliner / Makanan</option>
            <option value="craft">Kerajinan / Kerajinan Tangan</option>
            <option value="souvenir">Oleh-oleh / Cendera Mata</option>
            <option value="service">Jasa Wisata / Massage</option>
        </select>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Bisnis</label>
        <textarea name="description" rows="3"
            placeholder="Jelaskan mengenai menu atau layanan yang ditawarkan..."
            class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none resize-none"></textarea>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Rating Awal</label>
            <input type="number" step="0.1" name="rating" min="0" max="5" value="5.0"
                class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none">
        </div>
        <div>
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">ID Marker AR (Opsional)</label>
            <input type="text" name="ar_marker_id" placeholder="Contoh: UMKM_DEDARI_01"
                class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Latitude</label>
            <input type="text" name="latitude" readonly
                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold text-gray-400 uppercase">Longitude</label>
            <input type="text" name="longitude" readonly
                class="w-full rounded-xl bg-gray-50 border border-gray-200 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
    </div>

    <div class="flex flex-wrap gap-4 py-1">
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" name="is_active" value="1" checked
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
            <span class="text-sm font-semibold text-gray-700">Aktifkan Toko</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" id="umkm_is_accessible" name="is_accessible" value="1" checked
                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary">
            <span class="text-sm font-semibold text-gray-700">Akses Ramah Disabilitas</span>
        </label>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas</label>
        <textarea name="accessibility_notes" rows="2" placeholder="Contoh: Pintu masuk ramah kursi roda..."
            class="w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:outline-none resize-none">Pintu masuk landai, staf siap membantu akses disabilitas.</textarea>
    </div>

    <div class="flex gap-2 pt-2">
        <button type="submit"
            class="flex-1 rounded-xl bg-primary py-2.5 text-sm font-semibold text-white transition-all hover:bg-primary-600">Simpan</button>
        <button type="button" onclick="cancelEditor()"
            class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
    </div>
</form>

<script>
    function showOwnerDropdown() {
        document.getElementById('umkm-owner-dropdown').classList.remove('hidden');
    }

    function filterOwners(query) {
        const lowerQuery = query.toLowerCase();
        const options = document.querySelectorAll('.owner-option');
        options.forEach(opt => {
            const name = opt.getAttribute('data-name').toLowerCase();
            const email = opt.getAttribute('data-email').toLowerCase();
            if (name.includes(lowerQuery) || email.includes(lowerQuery)) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
    }

    function selectOwner(id, name) {
        document.getElementById('umkm-owner-user-id').value = id;
        document.getElementById('umkm-owner-name').value = name;
        document.getElementById('umkm-owner-search').value = name;
        document.getElementById('umkm-owner-dropdown').classList.add('hidden');
    }

    // Close dropdown on click outside
    document.addEventListener('click', function(e) {
        const searchInput = document.getElementById('umkm-owner-search');
        const dropdown = document.getElementById('umkm-owner-dropdown');
        if (searchInput && dropdown && !searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
