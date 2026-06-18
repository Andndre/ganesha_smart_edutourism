@extends('layouts.dashboard')

@section('title', 'Pemilik & Toko UMKM')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Akun Pemilik & Toko UMKM</h1>
        <p class="mt-0.5 text-sm text-gray-500">Kelola kredensial akun pemilik UMKM dan tautkan ke profil toko mereka.</p>
    </div>
    <button onclick="openCreateModal()" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
        Buat Akun Pemilik
    </button>
</div>

{{-- Owners Table --}}
<div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm max-w-5xl">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/50">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Pemilik</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Email</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Nomor Telepon</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Toko Tertaut</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($owners as $owner)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4 font-semibold text-charcoal">{{ $owner->name }}</td>
                        <td class="px-5 py-4 text-gray-500 font-mono text-xs">{{ $owner->email }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $owner->phone ?? '-' }}</td>
                        <td class="px-5 py-4">
                            @if ($owner->umkmProfile)
                                <span class="rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary-800">
                                    {{ $owner->umkmProfile->business_name }}
                                </span>
                            @else
                                <span class="text-xs italic text-gray-400">Belum ditautkan ke Toko</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <button onclick="openEditModal({{ json_encode($owner) }})" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-primary/10 hover:text-primary" title="Edit">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form method="POST" action="{{ route('admin.umkm.owners.destroy', $owner->id) }}" class="delete-form inline" data-confirm="Apakah Anda yakin ingin menghapus akun pemilik UMKM ini?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-warning/10 hover:text-warning" title="Hapus">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-gray-400">Belum ada akun pemilik UMKM.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Owner Modal Form --}}
<x-modal name="owner-modal" maxWidth="md">
    <div class="mb-4">
        <h3 id="modal-title" class="font-display text-lg font-bold text-charcoal">Buat Akun Pemilik UMKM</h3>
    </div>
    <form id="modal-form" method="POST" action="">
        @csrf
        <div id="method-container"></div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700">Nama Pemilik <span class="text-warning">*</span></label>
                <input type="text" name="name" id="field-name" required placeholder="Contoh: Wayan Sudira" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Email Login <span class="text-warning">*</span></label>
                <input type="email" name="email" id="field-email" required placeholder="Contoh: wayan@example.com" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700">Nomor Telepon</label>
                <input type="text" name="phone" id="field-phone" placeholder="Contoh: 08123456789" class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
            </div>
            <div>
                <label id="password-label" class="block text-sm font-semibold text-gray-700">Password <span class="text-warning">*</span></label>
                <div class="relative mt-1">
                    <input type="password" name="password" id="field-password" required class="w-full rounded-xl border border-gray-200 pl-4 pr-10 py-2.5 text-sm focus:border-primary focus:outline-none">
                    <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                        <svg id="eye-open-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644 10.68 10.68 0 0120.088 0 1.014 1.014 0 010 .644 10.68 10.68 0 01-20.088 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg id="eye-closed-icon" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.822 7.822L21 21m-2.228-2.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                <p id="password-help" class="mt-1 text-xs text-gray-400 hidden">* Biarkan kosong jika tidak ingin mengganti password.</p>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" onclick="closeModal()" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
            <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600">Simpan</button>
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
<script>
    const form = document.getElementById('modal-form');
    const modalTitle = document.getElementById('modal-title');
    const methodContainer = document.getElementById('method-container');
    const fieldName = document.getElementById('field-name');
    const fieldEmail = document.getElementById('field-email');
    const fieldPhone = document.getElementById('field-phone');
    const fieldPassword = document.getElementById('field-password');
    const passwordLabel = document.getElementById('password-label');
    const passwordHelp = document.getElementById('password-help');

    function openCreateModal() {
        modalTitle.innerText = "Buat Akun Pemilik UMKM";
        form.action = "{{ route('admin.umkm.owners.store') }}";
        methodContainer.innerHTML = "";
        
        fieldName.value = "";
        fieldEmail.value = "";
        fieldPhone.value = "";
        fieldPassword.value = "";
        fieldPassword.required = true;
        passwordLabel.innerHTML = 'Password <span class="text-warning">*</span>';
        passwordHelp.classList.add('hidden');
        resetPasswordVisibility();
        
        window.dispatchEvent(new CustomEvent('open-owner-modal'));
    }

    function openEditModal(owner) {
        modalTitle.innerText = "Edit Akun Pemilik UMKM";
        form.action = `/admin/umkm/owners/${owner.id}`;
        methodContainer.innerHTML = `@method('PUT')`;
        
        fieldName.value = owner.name;
        fieldEmail.value = owner.email;
        fieldPhone.value = owner.phone || "";
        fieldPassword.value = "";
        fieldPassword.required = false;
        passwordLabel.innerHTML = 'Password Baru';
        passwordHelp.classList.remove('hidden');
        resetPasswordVisibility();

        window.dispatchEvent(new CustomEvent('open-owner-modal'));
    }

    function togglePasswordVisibility() {
        const eyeOpen = document.getElementById('eye-open-icon');
        const eyeClosed = document.getElementById('eye-closed-icon');
        
        if (fieldPassword.type === 'password') {
            fieldPassword.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            fieldPassword.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }

    function resetPasswordVisibility() {
        fieldPassword.type = 'password';
        document.getElementById('eye-open-icon').classList.remove('hidden');
        document.getElementById('eye-closed-icon').classList.add('hidden');
    }

    function closeModal() {
        window.dispatchEvent(new CustomEvent('close-owner-modal'));
    }
</script>
@endpush
