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
                                <form method="POST" action="{{ route('admin.umkm.owners.destroy', $owner->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pemilik UMKM ini?')" class="inline">
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
<div id="owner-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-charcoal/50 backdrop-blur-sm p-4 justify-center">
    <div class="my-auto self-start w-full max-w-md rounded-2xl bg-white p-6 shadow-xl transition-all">
        <div class="mb-4 flex items-center justify-between">
            <h3 id="modal-title" class="font-display text-lg font-bold text-charcoal">Buat Akun Pemilik UMKM</h3>
            <button onclick="closeModal()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
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
                    <input type="password" name="password" id="field-password" required class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                    <p id="password-help" class="mt-1 text-xs text-gray-400 hidden">* Biarkan kosong jika tidak ingin mengganti password.</p>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const modal = document.getElementById('owner-modal');
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
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
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

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endpush
