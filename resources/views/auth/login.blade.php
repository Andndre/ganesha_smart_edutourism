@extends('layouts.auth')

@section('title', __('Masuk - Penglipuran Smart Tour'))

@section('content')
    <div class="bg-surface flex min-h-full flex-col justify-center px-6 py-12">

        {{-- Hero / Brand Header --}}
        <div class="mx-auto w-full max-w-sm text-center">
            <div class="mb-5 flex justify-center">
                <img src="{{ asset('icons/logo-penglipuran.png') }}" alt="Penglipuran Logo" class="h-20 w-auto object-contain">
            </div>

            <h1 class="font-display text-charcoal text-3xl font-bold tracking-tight">{{ __('Rahajeng Rauh') }}</h1>
            <p class="mt-2 text-sm leading-relaxed text-gray-500">
                {{ __('Masuk untuk memulai petualangan edukasi budaya Anda di Desa Penglipuran.') }}
            </p>
        </div>

        {{-- Main Container / Form Card --}}
        <div class="mx-auto mt-8 w-full max-w-sm">

            {{-- Flash Alert Messages --}}
            @if (session('success'))
                <div class="shadow-xs mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200/80 bg-emerald-50/90 p-4 text-sm text-emerald-800"
                    role="alert">
                    <i class="fas fa-circle-check mt-0.5 text-emerald-600"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="shadow-xs mb-5 flex items-start gap-3 rounded-2xl border border-red-200/80 bg-red-50/90 p-4 text-sm text-red-700"
                    role="alert">
                    <i class="fas fa-circle-exclamation mt-0.5 text-red-500"></i>
                    <div>
                        <span class="font-semibold">{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            {{-- Login Form --}}
            <form class="space-y-4" action="{{ route('login') }}" method="POST">
                @csrf

                <div class="space-y-1.5">
                    <label for="email"
                        class="text-xs font-bold uppercase tracking-wider text-gray-700">{{ __('Alamat Email') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        class="focus:border-primary focus:ring-primary/20 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 text-sm transition-all focus:outline-none focus:ring-2"
                        placeholder="{{ __('nama@email.com') }}" required>
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="password"
                            class="text-xs font-bold uppercase tracking-wider text-gray-700">{{ __('Password') }}</label>
                        <a href="{{ route('forgot-password') }}"
                            class="text-primary text-xs font-bold hover:underline">{{ __('Lupa password?') }}</a>
                    </div>
                    <div class="relative">
                        <input type="password" id="password" name="password"
                            class="focus:border-primary focus:ring-primary/20 w-full rounded-2xl border border-gray-200 bg-white px-4 py-3.5 pr-11 text-sm transition-all focus:outline-none focus:ring-2"
                            placeholder="••••••••" required>
                        <button type="button" id="toggle-password" aria-label="{{ __('Tampilkan password') }}"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg id="eye-open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <svg id="eye-closed" class="h-5 w-5" style="display: none;" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24" />
                                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68" />
                                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61" />
                                <line x1="2" y1="2" x2="22" y2="22" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="tap-target bg-primary shadow-primary/20 hover:bg-primary-600 mt-2 w-full rounded-2xl py-3.5 font-bold text-white shadow-lg transition-all active:scale-[0.98]">
                    {{ __('Masuk ke Aplikasi') }}
                </button>
            </form>

            {{-- Divider --}}
            <div class="relative my-6 flex items-center">
                <div class="grow border-t border-gray-200"></div>
                <span class="mx-4 shrink-0 text-xs font-semibold uppercase tracking-wider text-gray-400">
                    {{ __('atau masuk dengan') }}
                </span>
                <div class="grow border-t border-gray-200"></div>
            </div>

            {{-- Social Login --}}
            <div class="space-y-3">
                <a href="{{ route('auth.google') }}"
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
                    <span>{{ __('Lanjutkan dengan Google') }}</span>
                </a>
            </div>

            {{-- Register Link --}}
            <p class="mt-8 text-center text-sm font-medium text-gray-500">
                {{ __('Belum punya tiket/akun?') }}
                <a href="{{ route('register') }}"
                    class="text-primary hover:text-primary-600 font-bold transition-colors">{{ __('Daftar sekarang') }}</a>
            </p>

        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            function init() {
                const togglePassword = document.getElementById('toggle-password');
                const passwordInput = document.getElementById('password');
                const eyeOpen = document.getElementById('eye-open');
                const eyeClosed = document.getElementById('eye-closed');

                if (togglePassword && passwordInput && eyeOpen && eyeClosed) {
                    togglePassword.addEventListener('click', function() {
                        const isPassword = passwordInput.getAttribute('type') === 'password';
                        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                        eyeOpen.style.display = isPassword ? 'none' : 'block';
                        eyeClosed.style.display = isPassword ? 'block' : 'none';
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
