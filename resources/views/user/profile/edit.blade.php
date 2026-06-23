@extends('layouts.app')
@section('title', __('Ubah Profil - Penglipuran'))
@section('header_title', __('Ubah Profil'))

@section('content')
    <div class="px-4 py-6 md:px-8 lg:px-12">
        <div class="mx-auto max-w-4xl">

            @if (session('success'))
                <div
                    class="mb-6 flex items-center gap-2 rounded-2xl border border-green-100 bg-green-50 p-4 text-sm text-green-700">
                    <svg class="h-5 w-5 shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm text-red-600">
                    <div class="mb-1 font-bold">{{ __('Periksa kembali data Anda:') }}</div>
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Avatar Section --}}
            <div class="mb-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-charcoal mb-1 text-lg font-bold" style="font-family: 'Playfair Display', serif;">
                    {{ __('Foto Profil') }}</h2>
                <p class="mb-5 text-xs text-gray-500">{{ __('JPG, PNG, atau WebP. Maks. 2MB.') }}</p>

                <div x-data="{
                    previewUrl: '{{ $user->avatarUrl() }}',
                    fileName: '',
                    previewAndSubmit(event, form) {
                        const file = event.target.files[0];
                        if (!file) return;
                        const maxSize = 2 * 1024 * 1024;
                        if (file.size > maxSize) {
                            Swal.fire({ title: 'Ukuran File Terlalu Besar', text: 'Maksimal 2MB.', icon: 'warning', confirmButtonColor: '#1E5128', confirmButtonText: 'Mengerti', background: '#ffffff' });
                            event.target.value = '';
                            return;
                        }
                        this.fileName = file.name;
                        const reader = new FileReader();
                        reader.onload = (e) => { this.previewUrl = e.target.result; };
                        reader.readAsDataURL(file);
                        this.$nextTick(() => form.submit());
                    }
                }" class="flex flex-col items-center gap-5 sm:flex-row sm:items-start">

                    {{-- Avatar preview --}}
                    <div class="shrink-0 text-center">
                        <img :src="previewUrl" alt="{{ __('Avatar') }}"
                            class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-md">
                        <p class="mt-1 max-w-24 truncate text-xs text-gray-400" x-show="fileName" x-text="fileName"></p>
                    </div>

                    {{-- Upload & delete forms --}}
                    <div class="flex w-full flex-col gap-3 sm:pt-1">
                        <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data"
                            x-ref="avatarForm">
                            @csrf
                            <label
                                class="flex cursor-pointer items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-gray-200 p-4 transition-colors hover:border-primary hover:bg-green-50/50">
                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-500">{{ __('Pilih foto baru') }}</span>
                                <input type="file" name="avatar" accept="image/*" class="hidden"
                                    @change="previewAndSubmit($event, $refs.avatarForm)">
                            </label>
                        </form>

                        @if ($user->avatar_path && !str_starts_with($user->avatar_path, 'http'))
                            <form action="{{ route('profile.avatar.delete') }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="w-full rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-500 transition-colors hover:bg-red-100">
                                    {{ __('Hapus Foto') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Profile Info Form --}}
            <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <h2 class="text-charcoal mb-1 text-lg font-bold" style="font-family: 'Playfair Display', serif;">
                        {{ __('Informasi Profil') }}</h2>
                    <p class="text-xs text-gray-500">
                        {{ __('Perbarui data diri Anda untuk kenyamanan menjelajahi Desa Penglipuran.') }}</p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <!-- Name -->
                        <div>
                            <label for="name"
                                class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Nama Lengkap') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                                placeholder="{{ __('Masukkan nama lengkap Anda') }}">
                            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email"
                                class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Alamat Email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                                placeholder="nama@email.com">
                            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone"
                                class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Nomor Telepon (Opsional)') }}</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                                placeholder="{{ __('Contoh: 081234567890') }}">
                            @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <!-- Nationality -->
                        <div>
                            <label for="nationality"
                                class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Kebangsaan (Opsional)') }}</label>
                            <input type="text" name="nationality" id="nationality"
                                value="{{ old('nationality', $user->nationality) }}"
                                class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                                placeholder="{{ __('Contoh: Indonesia') }}">
                            @error('nationality')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <!-- Preferred Language -->
                        <div class="md:col-span-2">
                            <label for="preferred_language"
                                class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Bahasa Pilihan (Opsional)') }}</label>
                            <div class="relative">
                                <select name="preferred_language" id="preferred_language"
                                    class="focus:border-primary focus:ring-primary w-full appearance-none rounded-2xl border border-gray-200 bg-white p-4 pr-10 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1">
                                    <option value="" disabled
                                        {{ old('preferred_language', $user->preferred_language) == '' ? 'selected' : '' }}>
                                        {{ __('Pilih Bahasa') }}</option>
                                    <option value="id"
                                        {{ old('preferred_language', $user->preferred_language) == 'id' ? 'selected' : '' }}>
                                        {{ __('Bahasa Indonesia') }}</option>
                                    <option value="en"
                                        {{ old('preferred_language', $user->preferred_language) == 'en' ? 'selected' : '' }}>
                                        English</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            @error('preferred_language')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <div>
                        <h3 class="text-charcoal mb-1 text-base font-bold"
                            style="font-family: 'Playfair Display', serif;">
                            {{ __('Ubah Kata Sandi') }}</h3>
                        <p class="text-xs text-gray-500">
                            {{ __('Kosongkan jika Anda tidak ingin mengubah kata sandi Anda.') }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <!-- New Password -->
                        <div>
                            <label for="password"
                                class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Kata Sandi Baru (Opsional)') }}</label>
                            <input type="password" name="password" id="password"
                                class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                                placeholder="{{ __('Minimal 8 karakter') }}">
                            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation"
                                class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                                placeholder="{{ __('Masukkan kembali kata sandi baru') }}">
                            @error('password_confirmation')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                        <a href="{{ route('profile') }}"
                            class="text-charcoal flex h-12 w-full items-center justify-center rounded-2xl border border-gray-200 px-8 font-bold transition-all active:scale-[0.98] sm:w-auto">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit"
                            class="bg-primary shadow-primary/30 flex h-12 w-full items-center justify-center rounded-2xl px-8 font-bold text-white shadow-lg transition-all active:scale-[0.98] sm:w-auto">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection
