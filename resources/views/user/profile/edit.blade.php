@extends('layouts.app')
@section('title', __('Ubah Profil - Penglipuran'))
@section('header_title', __('Ubah Profil'))

@section('content')
    <div class="px-5 py-6">
        <div class="mb-6">
            <h2 class="text-charcoal mb-2 text-2xl font-bold" style="font-family: 'Playfair Display', serif;">
                {{ __('Informasi Profil') }}</h2>
            <p class="text-sm text-gray-500">
                {{ __('Perbarui data diri Anda untuk kenyamanan menjelajahi Desa Penglipuran.') }}</p>
        </div>

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

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name Field -->
            <div>
                <label for="name" class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Nama Lengkap') }}</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                    class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                    placeholder="{{ __('Masukkan nama lengkap Anda') }}">
            </div>

            <!-- Email Field -->
            <div>
                <label for="email" class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Alamat Email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                    class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                    placeholder="nama@email.com">
            </div>

            <!-- Phone Field -->
            <div>
                <label for="phone"
                    class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Nomor Telepon (Opsional)') }}</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                    class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                    placeholder="Contoh: 081234567890">
            </div>

            <!-- Nationality Field -->
            <div>
                <label for="nationality"
                    class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Kebangsaan (Opsional)') }}</label>
                <input type="text" name="nationality" id="nationality"
                    value="{{ old('nationality', $user->nationality) }}"
                    class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                    placeholder="Contoh: Indonesia">
            </div>

            <!-- Preferred Language Field -->
            <div>
                <label for="preferred_language"
                    class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Bahasa Pilihan (Opsional)') }}</label>
                <div class="relative">
                    <select name="preferred_language" id="preferred_language"
                        class="focus:border-primary focus:ring-primary bg-size-[1.25rem_1.25rem] bg-position-[right_1rem_center] w-full appearance-none rounded-2xl border border-gray-200 bg-white bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22none%22%3E%3Cpath%20d%3D%22M7%209l3%203%203-3%22%20stroke%3D%22%236b7280%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-no-repeat p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1">
                        <option value="" disabled
                            {{ old('preferred_language', $user->preferred_language) == '' ? 'selected' : '' }}>
                            {{ __('Pilih Bahasa') }}</option>
                        <option value="id"
                            {{ old('preferred_language', $user->preferred_language) == 'id' ? 'selected' : '' }}>
                            {{ __('Bahasa Indonesia') }}</option>
                        <option value="en"
                            {{ old('preferred_language', $user->preferred_language) == 'en' ? 'selected' : '' }}>English
                        </option>
                    </select>
                </div>
            </div>

            <!-- Divider -->
            <hr class="my-6 border-gray-100">

            <div class="mb-2">
                <h3 class="text-charcoal mb-1 text-lg font-bold" style="font-family: 'Playfair Display', serif;">
                    {{ __('Ubah Kata Sandi') }}</h3>
                <p class="text-xs text-gray-500">{{ __('Kosongkan jika Anda tidak ingin mengubah kata sandi Anda.') }}</p>
            </div>

            <!-- New Password Field -->
            <div>
                <label for="password"
                    class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Kata Sandi Baru (Opsional)') }}</label>
                <input type="password" name="password" id="password"
                    class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                    placeholder="Minimal 8 karakter">
            </div>

            <!-- New Password Confirmation Field -->
            <div>
                <label for="password_confirmation"
                    class="text-charcoal mb-1.5 block text-sm font-bold">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                    class="focus:border-primary focus:ring-primary w-full rounded-2xl border border-gray-200 bg-white p-4 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1"
                    placeholder="Masukkan kembali kata sandi baru">
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col gap-3 pt-4">
                <button type="submit"
                    class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98]">
                    {{ __('Simpan Perubahan') }}
                </button>
                <a href="{{ route('profile') }}"
                    class="text-charcoal flex h-14 w-full items-center justify-center rounded-2xl border border-gray-200 font-bold transition-all active:scale-[0.98]">
                    {{ __('Batal') }}
                </a>
            </div>
        </form>
    </div>
@endsection
