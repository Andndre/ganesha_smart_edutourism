@extends('layouts.auth')

@section('title', 'Daftar - Penglipuran Smart Tour')

@section('content')
    <div class="bg-surface flex min-h-full flex-col justify-center px-6 py-10">

        {{-- Hero / Brand Header --}}
        <div class="mx-auto w-full max-w-sm">
            <div class="bg-primary shadow-primary/30 mb-8 flex h-16 w-16 items-center justify-center rounded-2xl shadow-lg">
                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21V3m0 0l-5 5m5-5l5 5m-11 8h12" />
                </svg>
            </div>

            <h1 class="font-display text-charcoal text-3xl font-bold">Buat Akun</h1>
            <p class="mt-2 text-base leading-relaxed text-gray-500">Bergabunglah untuk memesan tiket dan menjelajahi desa
                secara interaktif.</p>
        </div>

        {{-- Register Form --}}
        <form class="mx-auto mt-8 w-full max-w-sm space-y-4" action="#" method="POST">

            <div>
                <label for="name" class="text-charcoal mb-1.5 block text-sm font-semibold">Nama Lengkap</label>
                <div class="relative">
                    <input type="text" id="name" name="name" required
                        class="focus:border-primary focus:ring-primary/20 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm placeholder-gray-400 outline-none transition-all focus:ring-2"
                        placeholder="Masukkan nama sesuai KTP/Paspor">
                </div>
            </div>

            <div>
                <label for="email" class="text-charcoal mb-1.5 block text-sm font-semibold">Email</label>
                <div class="relative">
                    <input type="email" id="email" name="email" required
                        class="focus:border-primary focus:ring-primary/20 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm placeholder-gray-400 outline-none transition-all focus:ring-2"
                        placeholder="nama@email.com">
                </div>
            </div>

            <div>
                <label for="phone" class="text-charcoal mb-1.5 block text-sm font-semibold">Nomor WhatsApp</label>
                <div class="relative">
                    <input type="tel" id="phone" name="phone" required
                        class="focus:border-primary focus:ring-primary/20 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm placeholder-gray-400 outline-none transition-all focus:ring-2"
                        placeholder="Contoh: 081234567890">
                </div>
            </div>

            <div>
                <label for="password" class="text-charcoal mb-1.5 block text-sm font-semibold">Kata Sandi</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required minlength="8"
                        class="focus:border-primary focus:ring-primary/20 block w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-sm placeholder-gray-400 outline-none transition-all focus:ring-2"
                        placeholder="Minimal 8 karakter">
                    <button type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-gray-400 hover:text-gray-600 focus:outline-none"
                        aria-label="Tampilkan kata sandi">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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
                    Saya menyetujui
                    <a href="#" class="text-primary hover:text-primary-600 font-bold transition-colors">Syarat &
                        Ketentuan</a> serta
                    <a href="#" class="text-primary hover:text-primary-600 font-bold transition-colors">Kebijakan
                        Privasi</a>.
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
