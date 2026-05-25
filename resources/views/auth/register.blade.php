@extends('layouts.auth')

@section('title', 'Daftar - Penglipuran Smart Tour')

@section('content')
    <div class="bg-surface flex min-h-full flex-col justify-center px-6 py-10">

        {{-- Hero / Brand Header --}}
        <div class="mx-auto w-full max-w-sm text-center">
            <div class="mb-6 flex justify-center">
                <img src="{{ asset('icons/logo-color.png') }}" alt="Penglipuran Logo" class="h-24 w-auto object-contain">
            </div>

            <h1 class="font-display text-charcoal text-3xl font-bold">Buat Akun</h1>
            <p class="mt-2 text-base leading-relaxed text-gray-500">Bergabunglah untuk memesan tiket dan menjelajahi desa
                secara interaktif.</p>
        </div>

        {{-- Register Form --}}
        <form class="mx-auto mt-8 w-full max-w-sm space-y-4" action="{{ route('register') }}" method="POST">
            @csrf

            @if($errors->any())
                <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm mb-4 border border-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="space-y-1.5">
                <label for="name" class="text-sm font-semibold text-gray-700">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                    class="w-full rounded-2xl border border-gray-200 px-4 py-3.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/50"
                    placeholder="Contoh: Andre Kusuma" required>
            </div>

            <div class="space-y-1.5">
                <label for="email" class="text-sm font-semibold text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    class="w-full rounded-2xl border border-gray-200 px-4 py-3.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/50"
                    placeholder="Masukkan email aktif" required>
            </div>

            <div class="space-y-1.5">
                <label for="password" class="text-sm font-semibold text-gray-700">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password"
                        class="w-full rounded-2xl border border-gray-200 px-4 py-3.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/50"
                        placeholder="Minimal 8 karakter" required>
                    <button type="button" id="toggle-password"
                        class="absolute right-4 top-1/2 -translate-y-1/2 z-10 text-gray-400 focus:outline-none">
                        <!-- Eye Icon (Open) -->
                        <svg id="eye-open-pw" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Eye Icon (Closed) -->
                        <svg id="eye-closed-pw" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="password_confirmation" class="text-sm font-semibold text-gray-700">Konfirmasi Password</label>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="w-full rounded-2xl border border-gray-200 px-4 py-3.5 text-sm transition-all focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/50"
                        placeholder="Ketik ulang password" required>
                    <button type="button" id="toggle-password-conf"
                        class="absolute right-4 top-1/2 -translate-y-1/2 z-10 text-gray-400 focus:outline-none">
                        <!-- Eye Icon (Open) -->
                        <svg id="eye-open-conf" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <!-- Eye Icon (Closed) -->
                        <svg id="eye-closed-conf" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex items-start gap-3 pt-2">
                <div class="flex h-5 items-center">
                    <input type="checkbox" id="terms" name="terms" required
                        class="h-4.5 w-4.5 text-primary focus:ring-primary cursor-pointer rounded border-gray-300">
                </div>
                <label for="terms" class="cursor-pointer text-sm leading-snug text-gray-600">
                    {{ __('Saya menyetujui') }}
                    <a href="{{ route('terms') }}" class="text-primary hover:text-primary-600 font-bold transition-colors">{{ __('Syarat & Ketentuan') }}</a> {{ __('serta') }}
                    <a href="{{ route('privacy') }}" class="text-primary hover:text-primary-600 font-bold transition-colors">{{ __('Kebijakan Privasi') }}</a>.
                </label>
            </div>

            <button type="submit"
                class="tap-target bg-primary shadow-primary/20 hover:bg-primary-600 mt-6 w-full rounded-xl py-3.5 font-bold text-white shadow-lg transition-all active:scale-[0.98]">
                Daftar Sekarang
            </button>
        </form>

        {{-- Divider --}}
        <div class="mx-auto mt-8 w-full max-w-sm">
            <div class="relative flex items-center">
                <div class="grow border-t border-gray-200"></div>
                <span class="mx-4 shrink-0 text-xs font-medium uppercase tracking-wider text-gray-400">atau daftar
                    dengan</span>
                <div class="grow border-t border-gray-200"></div>
            </div>
        </div>

        {{-- Social Register --}}
        <div class="mx-auto mt-6 w-full max-w-sm space-y-3">
            <button type="button"
                class="tap-target flex w-full items-center justify-center gap-3 rounded-xl border border-gray-200 bg-white py-3.5 transition-all hover:bg-gray-50 active:scale-[0.98]">
                <svg class="h-5 w-5" viewBox="0 0 24 24">
                    <path fill="#4285F4"
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                    <path fill="#34A853"
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                    <path fill="#FBBC05"
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                    <path fill="#EA4335"
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                </svg>
                <span class="text-charcoal text-sm font-bold">Daftar menggunakan Google</span>
            </button>
        </div>

        {{-- Login Link --}}
        <p class="mt-10 pb-8 text-center text-sm font-medium text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-primary hover:text-primary-600 font-bold transition-colors">Masuk di
                sini</a>
        </p>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            function init() {
                // Toggle Main Password
                const togglePassword = document.getElementById('toggle-password');
                const passwordInput = document.getElementById('password');
                const eyeOpenPw = document.getElementById('eye-open-pw');
                const eyeClosedPw = document.getElementById('eye-closed-pw');

                if (togglePassword && passwordInput) {
                    togglePassword.addEventListener('click', function () {
                        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordInput.setAttribute('type', type);

                        if (type === 'password') {
                            eyeOpenPw.classList.remove('hidden');
                            eyeClosedPw.classList.add('hidden');
                        } else {
                            eyeOpenPw.classList.add('hidden');
                            eyeClosedPw.classList.remove('hidden');
                        }
                    });
                }

                // Toggle Confirmation Password
                const togglePasswordConf = document.getElementById('toggle-password-conf');
                const passwordConfInput = document.getElementById('password_confirmation');
                const eyeOpenConf = document.getElementById('eye-open-conf');
                const eyeClosedConf = document.getElementById('eye-closed-conf');

                if (togglePasswordConf && passwordConfInput) {
                    togglePasswordConf.addEventListener('click', function () {
                        const type = passwordConfInput.getAttribute('type') === 'password' ? 'text' : 'password';
                        passwordConfInput.setAttribute('type', type);

                        if (type === 'password') {
                            eyeOpenConf.classList.remove('hidden');
                            eyeClosedConf.classList.add('hidden');
                        } else {
                            eyeOpenConf.classList.add('hidden');
                            eyeClosedConf.classList.remove('hidden');
                        }
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