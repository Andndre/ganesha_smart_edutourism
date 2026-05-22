@extends('layouts.app')
@section('title', 'Ubah Profil - Penglipuran')
@section('header_title', 'Ubah Profil')

@section('content')
<div class="px-5 py-6">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-charcoal mb-2" style="font-family: 'Playfair Display', serif;">Informasi Profil</h2>
        <p class="text-sm text-gray-500">Perbarui data diri Anda untuk kenyamanan menjelajahi Desa Penglipuran.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl bg-red-50 p-4 border border-red-100 text-sm text-red-600">
            <div class="font-bold mb-1">Periksa kembali data Anda:</div>
            <ul class="list-disc list-inside">
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
            <label for="name" class="block text-sm font-bold text-charcoal mb-1.5">Nama Lengkap</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                class="w-full bg-white border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors shadow-sm"
                placeholder="Masukkan nama lengkap Anda">
        </div>

        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-bold text-charcoal mb-1.5">Alamat Email</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                class="w-full bg-white border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors shadow-sm"
                placeholder="nama@email.com">
        </div>

        <!-- Phone Field -->
        <div>
            <label for="phone" class="block text-sm font-bold text-charcoal mb-1.5">Nomor Telepon (Opsional)</label>
            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                class="w-full bg-white border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors shadow-sm"
                placeholder="Contoh: 081234567890">
        </div>

        <!-- Nationality Field -->
        <div>
            <label for="nationality" class="block text-sm font-bold text-charcoal mb-1.5">Kebangsaan (Opsional)</label>
            <input type="text" name="nationality" id="nationality" value="{{ old('nationality', $user->nationality) }}"
                class="w-full bg-white border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors shadow-sm"
                placeholder="Contoh: Indonesia">
        </div>

        <!-- Preferred Language Field -->
        <div>
            <label for="preferred_language" class="block text-sm font-bold text-charcoal mb-1.5">Bahasa Pilihan (Opsional)</label>
            <div class="relative">
                <select name="preferred_language" id="preferred_language"
                    class="w-full bg-white border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors shadow-sm appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22none%22%3E%3Cpath%20d%3D%22M7%209l3%203%203-3%22%20stroke%3D%22%236b7280%22%20stroke-width%3D%221.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%2F%3E%3C%2Fsvg%3E')] bg-size-[1.25rem_1.25rem] bg-position-[right_1rem_center] bg-no-repeat">
                    <option value="" disabled {{ old('preferred_language', $user->preferred_language) == '' ? 'selected' : '' }}>Pilih Bahasa</option>
                    <option value="id" {{ old('preferred_language', $user->preferred_language) == 'id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                    <option value="en" {{ old('preferred_language', $user->preferred_language) == 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>
        </div>

        <!-- Divider -->
        <hr class="border-gray-100 my-6">

        <div class="mb-2">
            <h3 class="text-lg font-bold text-charcoal mb-1" style="font-family: 'Playfair Display', serif;">Ubah Kata Sandi</h3>
            <p class="text-xs text-gray-500">Kosongkan jika Anda tidak ingin mengubah kata sandi Anda.</p>
        </div>

        <!-- New Password Field -->
        <div>
            <label for="password" class="block text-sm font-bold text-charcoal mb-1.5">Kata Sandi Baru (Opsional)</label>
            <input type="password" name="password" id="password"
                class="w-full bg-white border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors shadow-sm"
                placeholder="Minimal 8 karakter">
        </div>

        <!-- New Password Confirmation Field -->
        <div>
            <label for="password_confirmation" class="block text-sm font-bold text-charcoal mb-1.5">Konfirmasi Kata Sandi Baru</label>
            <input type="password" name="password_confirmation" id="password_confirmation"
                class="w-full bg-white border border-gray-200 rounded-2xl p-4 text-sm focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors shadow-sm"
                placeholder="Masukkan kembali kata sandi baru">
        </div>

        <!-- Action Buttons -->
        <div class="pt-4 flex flex-col gap-3">
            <button type="submit" class="w-full bg-primary text-white font-bold h-14 rounded-2xl active:scale-[0.98] transition-all shadow-lg shadow-primary/30 flex items-center justify-center">
                Simpan Perubahan
            </button>
            <a href="{{ route('profile') }}" class="w-full border border-gray-200 text-charcoal font-bold h-14 rounded-2xl active:scale-[0.98] transition-all flex items-center justify-center">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
