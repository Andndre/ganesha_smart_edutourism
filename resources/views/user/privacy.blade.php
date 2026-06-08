@extends('layouts.auth')

@section('title', __('Kebijakan Privasi') . ' - Penglipuran Smart Tour')

@section('content')
    <div class="bg-surface min-h-full pb-12">
        {{-- Custom Page Header --}}
        <div class="bg-primary pt-sat sticky top-0 z-30 text-white shadow-md">
            <div class="mx-auto flex h-16 max-w-2xl items-center justify-between px-4">
                {{-- Back button --}}
                <button onclick="window.location.href = '{{ route('register') }}'"
                    class="tap-target -ml-2 flex h-10 w-10 items-center justify-center active:scale-95"
                    aria-label="{{ __('Kembali') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                {{-- Page Title --}}
                <h1 class="text-base font-bold tracking-wide">
                    {{ __('Kebijakan Privasi') }}
                </h1>

                {{-- Language Switcher --}}
                <div class="relative flex items-center gap-1.5">
                    <button onclick="location.replace('{{ route('privacy', ['locale' => 'id']) }}')"
                        class="{{ app()->getLocale() === 'id' ? 'bg-white text-primary shadow-sm' : 'bg-white/10 text-white hover:bg-white/20' }} rounded-lg px-2.5 py-1 text-xs font-bold transition-all">
                        ID
                    </button>
                    <button onclick="location.replace('{{ route('privacy', ['locale' => 'en']) }}')"
                        class="{{ app()->getLocale() === 'en' ? 'bg-white text-primary shadow-sm' : 'bg-white/10 text-white hover:bg-white/20' }} rounded-lg px-2.5 py-1 text-xs font-bold transition-all">
                        EN
                    </button>
                </div>
            </div>
        </div>

        {{-- Document Header --}}
        <div class="mx-auto max-w-2xl px-4 pt-8 text-center">
            <div class="bg-primary/10 text-primary mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h2 class="font-display text-charcoal text-2xl font-bold">
                {{ __('Kebijakan Privasi') }}
            </h2>
            <p class="mt-2 text-xs text-gray-500">
                {{ __('Terakhir diperbarui') }}: 25 Mei 2026
            </p>
        </div>

        {{-- Content Body --}}
        <div class="mx-auto mt-8 max-w-2xl px-4">
            <div
                class="space-y-6 rounded-3xl border border-gray-100 bg-white p-6 text-sm leading-relaxed text-gray-600 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)]">

                @if (app()->getLocale() === 'en')
                    {{-- English Content --}}
                    <div class="bg-primary/5 border-primary/10 text-primary rounded-2xl border p-4 text-xs">
                        <strong>Privacy First:</strong> Your privacy is extremely important to us. Ganesha Smart Edutourism
                        is
                        designed to protect your personal data while delivering a premium, immersive tourist experience.
                    </div>

                    <div class="space-y-4">
                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <svg class="text-primary/70 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Information We Collect
                            </h3>
                            <p class="mt-2 pl-7 text-gray-500">
                                We collect account information (name, email address) to manage tickets and track learning
                                achievements. When using map directions or AR scanning, the app utilizes your camera feed
                                and
                                device coordinates strictly in real-time. Camera frames are never stored or transmitted to
                                external servers.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <svg class="text-primary/70 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Data Security & Encryption
                            </h3>
                            <p class="mt-2 pl-7 text-gray-500">
                                Your account data and transaction flows are safeguarded with Secure Sockets Layer (SSL)
                                encryption. Passwords are hash-encrypted locally in our database using standard
                                industry-grade
                                cryptography (bcrypt).
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <svg class="text-primary/70 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Payments & Financial Transactions
                            </h3>
                            <p class="mt-2 pl-7 text-gray-500">
                                Payment gateways process transactional tasks independently. We do not store credit card
                                details
                                or bank account credentials on our database servers.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <svg class="text-primary/70 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Your Privacy Rights
                            </h3>
                            <p class="mt-2 pl-7 text-gray-500">
                                You hold total control over your personal profile data. You can access, edit, or terminate
                                your
                                profile registration details at any time from your profile dashboard settings. Camera
                                permissions for AR can be toggled on or off directly through your browser or device
                                settings.
                            </p>
                        </section>
                    </div>

                    <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <h4 class="text-charcoal text-sm font-bold">{{ __('Hubungi Kami') }}</h4>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Jika Anda memiliki pertanyaan tentang kebijakan ini, silakan hubungi tim dukungan kami.') }}
                        </p>
                        <span class="text-primary mt-2 block text-xs font-bold">support@penglipuran.go.id</span>
                    </div>
                @else
                    {{-- Indonesian Content (Default) --}}
                    <div class="bg-primary/5 border-primary/10 text-primary rounded-2xl border p-4 text-xs">
                        <strong>Mengutamakan Privasi:</strong> Privasi Anda sangat penting bagi kami. Ganesha Smart
                        Edutourism
                        dirancang untuk melindungi data pribadi Anda sekaligus menghadirkan pengalaman wisata budaya yang
                        interaktif dan premium.
                    </div>

                    <div class="space-y-4">
                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <svg class="text-primary/70 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                Informasi yang Kami Kumpulkan
                            </h3>
                            <p class="mt-2 pl-7 text-gray-500">
                                Kami mengumpulkan informasi akun dasar (nama, alamat email) untuk mengelola e-ticket dan
                                riwayat
                                pembelajaran budaya Anda. Ketika Anda menggunakan pemetaan luring atau pemindaian AR,
                                aplikasi
                                menggunakan kamera perangkat dan koordinat GPS secara langsung. Bingkai kamera hanya
                                diproses
                                secara real-time dan tidak pernah disimpan atau dikirim ke server luar.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <svg class="text-primary/70 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                Keamanan Data & Enkripsi
                            </h3>
                            <p class="mt-2 pl-7 text-gray-500">
                                Data akun dan alur transaksi Anda dilindungi dengan enkripsi Secure Sockets Layer (SSL).
                                Semua
                                kata sandi dienkripsi secara satu arah di basis data kami menggunakan kriptografi standar
                                industri (bcrypt).
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <svg class="text-primary/70 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Pemrosesan Transaksi Keuangan
                            </h3>
                            <p class="mt-2 pl-7 text-gray-500">
                                Gerbang pembayaran mitra kami memproses transaksi tiket secara independen. Kami sama sekali
                                tidak menyimpan data nomor kartu kredit atau detail otentikasi perbankan Anda di server
                                kami.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <svg class="text-primary/70 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Hak Privasi Anda
                            </h3>
                            <p class="mt-2 pl-7 text-gray-500">
                                Anda memiliki kontrol penuh atas data profil Anda. Anda berhak melihat, mengedit, atau
                                menghapus
                                informasi akun Anda kapan saja dari menu pengaturan profil. Izin kamera untuk fitur AR dapat
                                diaktifkan atau dinonaktifkan kapan saja melalui browser atau sistem operasi perangkat Anda.
                            </p>
                        </section>
                    </div>

                    <div class="mt-6 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <h4 class="text-charcoal text-sm font-bold">{{ __('Hubungi Kami') }}</h4>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Jika Anda memiliki pertanyaan tentang kebijakan ini, silakan hubungi tim dukungan kami.') }}
                        </p>
                        <span class="text-primary mt-2 block text-xs font-bold">support@penglipuran.go.id</span>
                    </div>
                @endif

                {{-- Action Footer --}}
                <div class="flex flex-col gap-3 border-t border-gray-100 pt-6 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="tap-target bg-primary hover:bg-primary-600 w-full rounded-xl py-3 text-center font-bold text-white shadow-md transition-all active:scale-95 sm:flex-1">
                        {{ __('Kembali ke Pendaftaran') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
