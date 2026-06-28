{{-- FORM 2: UMKM Profile --}}
<form id="form-umkm" action="{{ route('admin.umkm.profile.store') }}" method="POST" class="hidden space-y-4"
    x-data="{ locale: 'en' }">
    @csrf
    <div id="method-umkm"></div>

    {{-- Locale tabs --}}
    <div class="sticky top-0 z-10 -mx-6 mb-4 flex gap-2 border-b border-gray-100 bg-white px-6 py-3">
        <button @click="locale = 'en'"
            :class="locale === 'en' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
            class="rounded-xl px-4 py-2 text-sm font-semibold transition-all" type="button">English</button>
        <button @click="locale = 'id'"
            :class="locale === 'id' ? 'bg-primary text-white' : 'bg-gray-200 text-gray-600'"
            class="rounded-xl px-4 py-2 text-sm font-semibold transition-all" type="button">Indonesia</button>
    </div>

    {{-- Business Name --}}
    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Toko (EN) <span
                class="text-warning">*</span></label>
        <input type="text" name="business_name[en]" required placeholder="e.g. Dedari Shop"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Toko (ID) <span
                class="text-warning">*</span></label>
        <input type="text" name="business_name[id]" required placeholder="Contoh: Warung Dedari"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Pilih Akun Pemilik <span
                class="text-warning">*</span></label>
        <div class="relative">
            <input type="hidden" name="user_id" id="umkm-owner-user-id">
            <input type="text" id="umkm-owner-search" placeholder="Cari nama atau email pemilik..."
                class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 pr-10 text-sm focus:outline-none"
                onfocus="showOwnerDropdown()" oninput="filterOwners(this.value)" autocomplete="off">
            <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            {{-- Dropdown options --}}
            <div id="umkm-owner-dropdown"
                class="absolute left-0 right-0 z-50 mt-1 hidden max-h-48 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-lg">
                @forelse ($owners as $owner)
                    <div class="owner-option text-charcoal hover:bg-primary cursor-pointer px-4 py-2.5 text-sm transition-colors hover:text-white"
                        data-id="{{ $owner->id }}" data-name="{{ $owner->name }}" data-email="{{ $owner->email }}"
                        onclick="selectOwner({{ $owner->id }}, '{{ $owner->name }}')">
                        <div class="font-semibold">{{ $owner->name }}</div>
                        <div class="font-mono text-[10px] opacity-75">{{ $owner->email }}</div>
                    </div>
                @empty
                    <div class="px-4 py-3 text-sm italic text-gray-400">Belum ada akun pemilik UMKM.</div>
                @endforelse
            </div>
        </div>
        <button type="button" onclick="document.getElementById('modal-create-owner').classList.remove('hidden')"
            class="text-primary hover:text-primary/80 mt-2 inline-flex items-center gap-1.5 text-xs font-semibold transition-colors">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Buat Akun Baru
        </button>
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Nama Pemilik Toko <span
                class="text-warning">*</span></label>
        <input type="text" name="owner_name" id="umkm-owner-name" required readonly
            placeholder="Akan terisi otomatis saat memilih pemilik..."
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 focus:outline-none">
    </div>

    <div x-show="locale === 'en'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Bisnis (EN)</label>
        <x-tiptap-editor name="description[en]" id="umkm-desc-en"
            placeholder="Describe the products or services offered..." />
    </div>
    <div x-show="locale === 'id'">
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Deskripsi Bisnis (ID)</label>
        <x-tiptap-editor name="description[id]" id="umkm-desc-id"
            placeholder="Jelaskan mengenai menu atau layanan yang ditawarkan..." />
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-semibold text-gray-700">Rating Awal</label>
        <input type="number" step="0.1" name="rating" min="0" max="5" value="5.0"
            class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-400">Latitude</label>
            <input type="text" name="latitude" readonly
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-xs font-semibold uppercase text-gray-400">Longitude</label>
            <input type="text" name="longitude" readonly
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-500 focus:outline-none">
        </div>
    </div>

    <div class="flex flex-wrap gap-4 py-1">
        <label class="flex cursor-pointer select-none items-center gap-2">
            <input type="checkbox" name="is_active" value="1" checked
                class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300">
            <span class="text-sm font-semibold text-gray-700">Aktifkan Toko</span>
        </label>
        <label class="flex cursor-pointer select-none items-center gap-2">
            <input type="checkbox" id="umkm_is_accessible" name="is_accessible" value="1" checked
                class="text-primary focus:ring-primary h-4 w-4 rounded border-gray-300">
            <span class="text-sm font-semibold text-gray-700">Akses Ramah Disabilitas</span>
        </label>
    </div>

    <div class="accessibility-notes-container">
        <div x-show="locale === 'en'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (EN)</label>
            <textarea name="accessibility_notes[en]" id="umkm-accessibility-notes-en" rows="2"
                placeholder="e.g. Ramp entrance, staff ready to help..."
                class="focus:border-primary w-full resize-none rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">Ramp entrance, staff ready to help with accessibility.</textarea>
        </div>
        <div x-show="locale === 'id'">
            <label class="mb-1.5 block text-sm font-semibold text-gray-700">Catatan Aksesibilitas (ID)</label>
            <textarea name="accessibility_notes[id]" id="umkm-accessibility-notes-id" rows="2"
                placeholder="Contoh: Pintu masuk ramah kursi roda..."
                class="focus:border-primary w-full resize-none rounded-xl border border-gray-200 px-4 py-2 text-sm focus:outline-none">Pintu masuk landai, staf siap membantu akses disabilitas.</textarea>
        </div>
    </div>

</form>

{{-- Modal: Create Owner Inline (outside form to avoid nested <form>) --}}
<div id="modal-create-owner" class="z-999 fixed inset-0 hidden bg-black/40 backdrop-blur-sm" style="display:none">
    <div class="flex h-full items-center justify-center" onclick="closeCreateOwnerModal()">
        <div class="mx-4 w-full max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-xl"
            onclick="event.stopPropagation()">
            <div class="flex items-center justify-between">
                <h3 class="text-charcoal text-lg font-bold">Buat Akun Pemilik Baru</h3>
                <button type="button" onclick="closeCreateOwnerModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="form-create-owner" onsubmit="submitCreateOwner(event)" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700">Nama <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name" required placeholder="Nama lengkap pemilik"
                        class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700">Email <span
                            class="text-warning">*</span></label>
                    <input type="email" name="email" required placeholder="email@contoh.com"
                        id="create-owner-email" onblur="checkOwnerEmail(this.value)"
                        class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    <p id="email-status" class="mt-1 hidden text-xs"></p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700">No. Telepon</label>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                        class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-700">Password <span
                            class="text-warning">*</span></label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter" minlength="8"
                        class="focus:border-primary w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                </div>
                <div id="create-owner-error"
                    class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs text-red-600"></div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeCreateOwnerModal()"
                        class="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 transition-colors hover:bg-gray-100">Batal</button>
                    <button type="submit" id="btn-create-owner"
                        class="bg-primary hover:bg-primary/90 rounded-xl px-5 py-2 text-sm font-semibold text-white transition-colors disabled:cursor-not-allowed disabled:opacity-50">
                        Buat Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // --- Owner Dropdown ---
    function showOwnerDropdown() {
        document.getElementById('umkm-owner-dropdown').classList.remove('hidden');
    }

    function filterOwners(query) {
        const lowerQuery = query.toLowerCase();
        const options = document.querySelectorAll('.owner-option');
        options.forEach(opt => {
            const name = opt.getAttribute('data-name').toLowerCase();
            const email = opt.getAttribute('data-email').toLowerCase();
            opt.style.display = (name.includes(lowerQuery) || email.includes(lowerQuery)) ? 'block' : 'none';
        });
    }

    function selectOwner(id, name) {
        document.getElementById('umkm-owner-user-id').value = id;
        document.getElementById('umkm-owner-name').value = name;
        document.getElementById('umkm-owner-search').value = name;
        document.getElementById('umkm-owner-dropdown').classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
        const searchInput = document.getElementById('umkm-owner-search');
        const dropdown = document.getElementById('umkm-owner-dropdown');
        if (searchInput && dropdown && !searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // --- Create Owner Modal ---
    const CHECK_EMAIL_URL = '{{ route('admin.umkm.owners.check-email') }}';
    const STORE_OWNER_URL = '{{ route('admin.umkm.owners.store.json') }}';

    function openCreateOwnerModal() {
        document.getElementById('modal-create-owner').style.display = 'block';
    }

    function closeCreateOwnerModal() {
        document.getElementById('modal-create-owner').style.display = 'none';
        document.getElementById('form-create-owner').reset();
        const status = document.getElementById('email-status');
        status.classList.add('hidden');
        document.getElementById('create-owner-error').classList.add('hidden');
    }

    // Open modal button
    document.querySelector('[onclick*="modal-create-owner"]')
        ?.setAttribute('onclick', 'openCreateOwnerModal()');

    // Close on backdrop click
    document.getElementById('modal-create-owner').addEventListener('click', function(e) {
        if (e.target === this) closeCreateOwnerModal();
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeCreateOwnerModal();
    });

    // --- Email uniqueness check ---
    let emailCheckTimer;
    async function checkOwnerEmail(email) {
        const status = document.getElementById('email-status');
        if (!email) {
            status.classList.add('hidden');
            return;
        }

        clearTimeout(emailCheckTimer);
        emailCheckTimer = setTimeout(async () => {
            try {
                const res = await fetch(CHECK_EMAIL_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        email
                    }),
                });
                const data = await res.json();
                status.classList.remove('hidden');
                if (data.taken) {
                    status.className = 'mt-1 text-xs text-red-600';
                    status.textContent = 'Email sudah digunakan akun lain.';
                } else {
                    status.className = 'mt-1 text-xs text-green-600';
                    status.textContent = 'Email tersedia.';
                }
            } catch (e) {
                /* silent */ }
        }, 400);
    }

    // --- Submit new owner ---
    async function submitCreateOwner(event) {
        event.preventDefault();
        const form = event.target;
        const btn = document.getElementById('btn-create-owner');
        const errorBox = document.getElementById('create-owner-error');

        btn.disabled = true;
        btn.textContent = 'Membuat...';
        errorBox.classList.add('hidden');

        const formData = new FormData(form);

        try {
            const res = await fetch(STORE_OWNER_URL, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            });
            const data = await res.json();

            if (!res.ok) {
                const messages = data.errors ?
                    Object.values(data.errors).flat().join('\n') :
                    (data.message || 'Terjadi kesalahan.');
                errorBox.textContent = messages;
                errorBox.classList.remove('hidden');
                return;
            }

            // Add new owner to dropdown
            const dropdown = document.getElementById('umkm-owner-dropdown');
            const emptyMsg = dropdown.querySelector('.italic');
            if (emptyMsg) emptyMsg.remove();

            const opt = document.createElement('div');
            opt.className =
                'owner-option px-4 py-2.5 text-sm text-charcoal hover:bg-primary hover:text-white cursor-pointer transition-colors';
            opt.dataset.id = data.owner.id;
            opt.dataset.name = data.owner.name;
            opt.dataset.email = data.owner.email;
            opt.onclick = () => selectOwner(data.owner.id, data.owner.name);
            opt.innerHTML =
                `<div class="font-semibold">${data.owner.name}</div><div class="text-[10px] opacity-75 font-mono">${data.owner.email}</div>`;
            dropdown.prepend(opt);

            // Auto-select
            selectOwner(data.owner.id, data.owner.name);
            closeCreateOwnerModal();

            // Haptic feedback
            if (navigator.vibrate) navigator.vibrate(50);
        } catch (e) {
            errorBox.textContent = 'Gagal menghubungi server.';
            errorBox.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Buat Akun';
        }
    }
</script>
