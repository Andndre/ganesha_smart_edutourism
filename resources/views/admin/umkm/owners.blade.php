@extends('layouts.dashboard')

@section('title', 'Pemilik & Toko UMKM')

@section('content')

    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-charcoal text-2xl font-bold">Akun Pemilik & Toko UMKM</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola kredensial akun pemilik UMKM dan tautkan ke profil toko mereka.
            </p>
        </div>
        <button onclick="openCreateModal()"
            class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Akun Pemilik
        </button>
    </div>

    <x-dashboard.table title="Pemilik UMKM" :columns="['Nama pemilik', 'Email', 'Nomor telepon', 'Toko tertaut', ['label' => 'Aksi', 'align' => 'right']]" :paginator="$owners">
        <x-slot:toolbar>
            <x-dashboard.table-filters :action="route('admin.umkm.owners')" id="owners-filters" search-name="search" label="Cari pemilik UMKM" placeholder="Nama, email, telepon, atau toko" :filtered="request()->filled('search')">
            </x-dashboard.table-filters>
        </x-slot:toolbar>

        @forelse ($owners as $owner)
            <tr>
                <th scope="row" class="text-charcoal font-semibold">{{ $owner->name }}</th>
                <td class="font-mono text-xs text-gray-500">{{ $owner->email }}</td>
                <td class="text-gray-600">{{ $owner->phone ?? '-' }}</td>
                <td>
                    @if ($owner->umkmProfile)
                        <div class="flex flex-wrap items-center gap-2">
                            <x-dashboard.table-badge tone="success">{{ $owner->umkmProfile->business_name }}</x-dashboard.table-badge>
                            <span class="text-xs font-semibold text-primary">{{ $owner->umkmProfile->isHouse() ? 'Rumah + UMKM' : 'UMKM Usaha' }}</span>
                            @if($owner->umkmProfile->isHouse() && $owner->umkmProfile->traditional_house_id)
                                <a href="{{ route('admin.houses.edit', $owner->umkmProfile->traditional_house_id) }}" class="inline-flex min-h-12 items-center text-xs font-semibold text-primary underline">Kelola rumah dan pengelola</a>
                            @endif
                            <x-dashboard.table-badge :tone="$owner->umkmProfile->has_map_pin ? 'success' : 'warning'">{{ $owner->umkmProfile->has_map_pin ? 'Ada di peta' : 'Belum ada titik peta' }}</x-dashboard.table-badge>
                        </div>
                    @else
                        <span class="text-xs italic text-gray-500">Belum ditautkan ke Toko</span>
                    @endif
                </td>
                <td>
                    <div class="table-actions">
                        <x-dashboard.table-action onclick="openEditModal({{ json_encode($owner) }})">Ubah</x-dashboard.table-action>
                        <form method="POST" action="{{ route('admin.umkm.owners.destroy', $owner->id) }}"
                            class="delete-form inline"
                            data-confirm="{{ 'Apakah Anda yakin ingin menghapus akun pemilik UMKM ini?' }}">
                            @csrf
                            @method('DELETE')
                            <x-dashboard.table-action type="submit"
                                variant="danger">Hapus</x-dashboard.table-action>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <x-dashboard.table-empty :colspan="5" :filtered="request()->filled('search')" message="Belum ada akun pemilik UMKM." />
        @endforelse
    </x-dashboard.table>

    @if ($unmappedProfiles->total() > 0)
        <div class="mt-8">
            <x-dashboard.table title="Toko tanpa titik peta" :columns="['Toko', 'Akun pemilik', ['label' => 'Produk', 'align' => 'right'], ['label' => 'Aksi', 'align' => 'right']]" :paginator="$unmappedProfiles">
                <x-slot:toolbar>
                    <p class="text-sm text-gray-600">Profil toko ini belum memiliki titik di peta. Menghapus toko juga menghapus produknya; akun pemilik tetap tersedia.</p>
                </x-slot:toolbar>
                @forelse ($unmappedProfiles as $profile)
                    <tr>
                        <th scope="row">
                            <p>{{ translateValue($profile->business_name) }}</p>
                            <x-dashboard.table-badge :tone="$profile->is_active ? 'success' : 'neutral'" class="mt-1">{{ $profile->is_active ? 'Aktif' : 'Nonaktif' }}</x-dashboard.table-badge>
                        </th>
                        <td>
                            <p>{{ $profile->user?->name ?? $profile->owner_name }}</p>
                            <p class="mt-1 text-xs text-gray-600">{{ $profile->user?->email ?? 'Belum ada akun tertaut' }}</p>
                        </td>
                        <td class="table-number">{{ $profile->products_count }}</td>
                        <td>
                            <div class="table-actions">
                                <form action="{{ route('admin.umkm.profile.destroy', $profile->id) }}" method="POST"
                                    class="delete-form" data-confirm="Hapus toko ini beserta produknya? Akun pemilik tetap tersedia.">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to" value="owners">
                                    <x-dashboard.table-action type="submit" variant="danger">Hapus toko</x-dashboard.table-action>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <x-dashboard.table-empty :colspan="4" message="Tidak ada toko di halaman ini." hint="Pilih halaman sebelumnya untuk melihat toko lainnya." />
                @endforelse
            </x-dashboard.table>
        </div>
    @endif

    {{-- Owner Modal Form --}}
    <x-modal name="owner-modal" maxWidth="md" desktopLayout="drawer">
        <div class="mb-4">
            <h3 id="modal-title" class="font-display text-charcoal text-lg font-bold">Buat Akun Pemilik UMKM</h3>
        </div>
        <form id="modal-form" method="POST" action="">
            @csrf
            <div id="method-container"></div>
            <input type="hidden" name="owner_id" id="field-owner-id" value="">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nama Pemilik <span
                            class="text-warning">*</span></label>
                    <input type="text" name="name" id="field-name" required placeholder="Contoh: Wayan Sudira"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Email Login <span
                            class="text-warning">*</span></label>
                    <input type="email" name="email" id="field-email" required placeholder="Contoh: wayan@example.com"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nomor Telepon</label>
                    <input type="text" name="phone" id="field-phone" placeholder="Contoh: 08123456789"
                        class="focus:border-primary mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:outline-none">
                    @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label id="password-label" class="block text-sm font-semibold text-gray-700">Password <span
                            class="text-warning">*</span></label>
                    <div class="relative mt-1">
                        <input type="password" name="password" id="field-password" required
                            class="focus:border-primary w-full rounded-xl border border-gray-200 py-2.5 pl-4 pr-10 text-sm focus:outline-none">
                        <button type="button" onclick="togglePasswordVisibility()"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-600">
                            <svg id="eye-open-icon" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 010-.644 10.68 10.68 0 0120.088 0 1.014 1.014 0 010 .644 10.68 10.68 0 01-20.088 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg id="eye-closed-icon" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.822 7.822L21 21m-2.228-2.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    <p id="password-help" class="mt-1 hidden text-xs text-gray-500">* Biarkan kosong jika tidak ingin
                        mengganti password.</p>
                    @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal()"
                    class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">Batal</button>
                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg">Simpan</button>
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
            document.getElementById('field-owner-id').value = "";

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
            document.getElementById('field-owner-id').value = owner.id;

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

        @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            window.dispatchEvent(new CustomEvent('open-owner-modal'));
            @if(old('_method') == 'PUT')
                form.action = "/admin/umkm/owners/{{ old('owner_id') }}";
                methodContainer.innerHTML = `@method('PUT')`;
                modalTitle.innerText = "Edit Akun Pemilik UMKM";
                document.getElementById('field-owner-id').value = "{{ old('owner_id') }}";
                fieldPassword.required = false;
                passwordLabel.innerHTML = 'Password Baru';
                passwordHelp.classList.remove('hidden');
            @else
                form.action = "{{ route('admin.umkm.owners.store') }}";
                methodContainer.innerHTML = "";
                modalTitle.innerText = "Buat Akun Pemilik UMKM";
                document.getElementById('field-owner-id').value = "";
                fieldPassword.required = true;
                passwordLabel.innerHTML = 'Password <span class="text-warning">*</span>';
                passwordHelp.classList.add('hidden');
            @endif
            fieldName.value = @json(old('name', ''));
            fieldEmail.value = @json(old('email', ''));
            fieldPhone.value = @json(old('phone', ''));
        });
        @endif
    </script>
@endpush
