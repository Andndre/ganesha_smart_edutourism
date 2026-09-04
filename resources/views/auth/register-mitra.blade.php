@extends('layouts.auth')

@section('title', __('Daftar Mitra UMKM - Penglipuran Smart Tour'))

@section('content')
    <div class="bg-surface flex min-h-full flex-col justify-center px-6 py-10">

        {{-- Hero / Brand Header --}}
        <div class="mx-auto w-full max-w-sm text-center">
            <div class="mb-5 flex justify-center">
                <img src="{{ asset('icons/logo-penglipuran.png') }}" alt="Penglipuran Logo" class="h-20 w-auto object-contain">
            </div>

            <h1 class="text-charcoal text-2xl font-bold tracking-tight">{{ __('Daftar Mitra UMKM') }}</h1>
            <p class="mt-2 text-sm leading-relaxed text-gray-500">
                {{ __('Daftarkan usaha Anda untuk terhubung langsung dengan wisatawan di Desa Penglipuran.') }}
            </p>
        </div>

        {{-- Main Container / Form Card --}}
        <div class="mx-auto mt-8 w-full max-w-sm">

            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="shadow-xs mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200/80 bg-emerald-50/90 p-4 text-sm text-emerald-800"
                    role="alert">
                    <svg class="h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="shadow-xs mb-5 flex items-start gap-3 rounded-2xl border border-red-200/80 bg-red-50/90 p-4 text-sm text-red-700"
                    role="alert">
                    <svg class="h-5 w-5 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <span class="font-semibold">{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            {{-- Fast Track: Google Register for UMKM --}}
            <div class="space-y-3">
                <a href="{{ route('auth.google', ['intent' => 'umkm']) }}"
                    class="tap-target text-charcoal shadow-xs flex w-full items-center justify-center gap-3 rounded-2xl border border-gray-200 bg-white py-3.5 text-sm font-bold transition-all hover:bg-gray-50 active:scale-[0.98]">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                            d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                    </svg>
                    <span>{{ __('Daftar menggunakan Google') }}</span>
                </a>
            </div>

            {{-- Divider --}}
            <div class="relative my-6 flex items-center">
                <div class="grow border-t border-gray-200"></div>
                <span class="mx-4 shrink-0 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    {{ __('atau daftar dengan formulir') }}
                </span>
                <div class="grow border-t border-gray-200"></div>
            </div>

            {{-- Registration Form --}}
            <form action="{{ route('mitra.register.store') }}" method="POST" class="space-y-4">
                @csrf

                {{-- Nama Lengkap Pemilik --}}
                <div class="space-y-1.5">
                    <label for="name" class="text-xs font-bold uppercase tracking-wider text-gray-700">
                        {{ __('Nama Lengkap Pemilik') }}
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                        class="focus:border-primary focus:ring-primary/20 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm transition-all focus:outline-none focus:ring-2"
                        placeholder="{{ __('Nama sesuai identitas') }}" required>
                </div>

                {{-- Nama Toko / Usaha --}}
                <div class="space-y-1.5">
                    <label for="business_name" class="text-xs font-bold uppercase tracking-wider text-gray-700">
                        {{ __('Nama Usaha / Toko') }}
                    </label>
                    <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}"
                        class="focus:border-primary focus:ring-primary/20 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm transition-all focus:outline-none focus:ring-2"
                        placeholder="{{ __('Nama warung atau toko Anda') }}" required>
                </div>

                {{-- Nomor WhatsApp --}}
                <div class="space-y-1.5">
                    <label for="phone" class="text-xs font-bold uppercase tracking-wider text-gray-700">
                        {{ __('Nomor WhatsApp') }}
                    </label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                        class="focus:border-primary focus:ring-primary/20 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm transition-all focus:outline-none focus:ring-2"
                        placeholder="08xxxxxxxxxx" required>
                </div>

                {{-- Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-bold uppercase tracking-wider text-gray-700">
                        {{ __('Alamat Email') }}
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="focus:border-primary focus:ring-primary/20 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm transition-all focus:outline-none focus:ring-2"
                        placeholder="nama@email.com" required>
                </div>

                {{-- Password --}}
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-bold uppercase tracking-wider text-gray-700">
                        {{ __('Password') }}
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="focus:border-primary focus:ring-primary/20 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 pr-11 text-sm transition-all focus:outline-none focus:ring-2"
                            placeholder="{{ __('Minimal 8 karakter') }}" required>
                        <button type="button" id="toggle-password" aria-label="{{ __('Tampilkan password') }}"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg id="eye-open-pw" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg id="eye-closed-pw" class="h-5 w-5" style="display: none;" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                <line x1="2" y1="2" x2="22" y2="22" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Konfirmasi Password --}}
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="text-xs font-bold uppercase tracking-wider text-gray-700">
                        {{ __('Konfirmasi Password') }}
                    </label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="focus:border-primary focus:ring-primary/20 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 pr-11 text-sm transition-all focus:outline-none focus:ring-2"
                            placeholder="{{ __('Ketik ulang password') }}" required>
                        <button type="button" id="toggle-password-conf"
                            aria-label="{{ __('Tampilkan konfirmasi password') }}"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg id="eye-open-conf" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg id="eye-closed-conf" class="h-5 w-5" style="display: none;" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                <line x1="2" y1="2" x2="22" y2="22" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Terms & Agreement --}}
                <div class="flex items-start gap-3 pt-2">
                    <div class="flex h-5 items-center">
                        <input type="checkbox" id="terms" name="terms" required
                            class="h-4.5 w-4.5 text-primary focus:ring-primary cursor-pointer rounded border-gray-300">
                    </div>
                    <label for="terms" class="cursor-pointer text-xs leading-relaxed text-gray-600">
                        {{ __('Saya menyatakan data usaha ini benar dan menyetujui ketentuan kemitraan UMKM Desa Penglipuran.') }}
                    </label>
                </div>

                {{-- Submit Button --}}
                <button type="submit"
                    class="tap-target bg-primary shadow-primary/20 hover:bg-primary-600 mt-2 w-full rounded-2xl py-3.5 font-bold text-white shadow-lg transition-all active:scale-[0.98]">
                    {{ __('Daftar Sebagai Mitra UMKM') }}
                </button>
            </form>

            {{-- Switch to Login --}}
            <p class="mt-8 text-center text-sm font-medium text-gray-500">
                {{ __('Sudah terdaftar sebagai Mitra?') }}
                <a href="{{ route('login') }}" class="text-primary hover:text-primary-600 font-bold transition-colors">
                    {{ __('Masuk di sini') }}
                </a>
            </p>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            function init() {
                // Toggle Main Password
                const togglePassword = document.getElementById('toggle-password');
                const passwordInput = document.getElementById('password');
                const eyeOpenPw = document.getElementById('eye-open-pw');
                const eyeClosedPw = document.getElementById('eye-closed-pw');

                if (togglePassword && passwordInput && eyeOpenPw && eyeClosedPw) {
                    togglePassword.addEventListener('click', function() {
                        const isPassword = passwordInput.getAttribute('type') === 'password';
                        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                        eyeOpenPw.style.display = isPassword ? 'none' : 'block';
                        eyeClosedPw.style.display = isPassword ? 'block' : 'none';
                    });
                }

                // Toggle Confirmation Password
                const togglePasswordConf = document.getElementById('toggle-password-conf');
                const passwordConfInput = document.getElementById('password_confirmation');
                const eyeOpenConf = document.getElementById('eye-open-conf');
                const eyeClosedConf = document.getElementById('eye-closed-conf');

                if (togglePasswordConf && passwordConfInput && eyeOpenConf && eyeClosedConf) {
                    togglePasswordConf.addEventListener('click', function() {
                        const isPassword = passwordConfInput.getAttribute('type') === 'password';
                        passwordConfInput.setAttribute('type', isPassword ? 'text' : 'password');
                        eyeOpenConf.style.display = isPassword ? 'none' : 'block';
                        eyeClosedConf.style.display = isPassword ? 'block' : 'none';
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endpush
