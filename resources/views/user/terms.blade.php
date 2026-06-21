@extends('layouts.auth')

@section('title', __('Syarat & Ketentuan') . ' - Penglipuran Smart Tour')

@section('content')
    <div class="bg-surface min-h-full pb-12">
        {{-- Custom Page Header --}}
        <div class="bg-primary pt-sat sticky top-0 z-30 text-white shadow-md">
            <div class="mx-auto flex h-16 max-w-2xl items-center justify-between px-4">
                {{-- Back button --}}
                <button onclick=" window.location.href = '{{ route('register') }}'"
                    class="tap-target -ml-2 flex h-10 w-10 items-center justify-center transition-all active:scale-95"
                    aria-label="{{ __('Kembali') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                {{-- Page Title --}}
                <h1 class="text-base font-bold tracking-wide">
                    {{ __('Syarat & Ketentuan') }}
                </h1>

                {{-- Language Switcher Dropdown/Buttons --}}
                <div class="relative flex items-center gap-1.5">
                    <button onclick="location.replace('{{ route('terms', ['locale' => 'id']) }}')"
                        class="{{ app()->getLocale() === 'id' ? 'bg-white text-primary shadow-sm' : 'bg-white/10 text-white hover:bg-white/20' }} rounded-lg px-2.5 py-1 text-xs font-bold transition-all">
                        ID
                    </button>
                    <button onclick="location.replace('{{ route('terms', ['locale' => 'en']) }}')"
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
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h2 class="font-display text-charcoal text-2xl font-bold">
                {{ __('Syarat & Ketentuan Penggunaan') }}
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
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-4 text-xs text-amber-800">
                        <strong>Important:</strong> Please read these Terms & Conditions carefully before using the
                        Penglipuran
                        Smart Tour application. By registering an account, you agree to comply with all terms specified
                        herein.
                    </div>

                    <div class="space-y-4">
                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">1</span>
                                Account Registration
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                To use the booking and interactive tourism features, you must register a valid account. You
                                are
                                responsible for maintaining the confidentiality of your password and all activities under
                                your
                                account.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">2</span>
                                Ticket Booking & Tour Packages
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                All bookings and transactions made through this application are final. E-tickets will be
                                issued
                                immediately upon verification of payment and must be shown at the village entry gate for QR
                                scanning.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">3</span>
                                AR Scan & Location Permissions
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                The Augmented Reality (AR) feature requires camera and geolocation access to project
                                interactive, culture-rich content within the village coordinates. Geolocation data is only
                                used
                                locally to load spatial landmarks.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">4</span>
                                Offline Maps & Caching
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                This app offers robust offline functionalities, saving essential travel tickets, maps, and
                                cultural documents locally in your browser cache. Do not clear your browser cache while
                                exploring offline to prevent data loss.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">5</span>
                                Respect Cultural Heritage
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Penglipuran is a sacred traditional village. Users agree to respect local Balinese
                                traditions,
                                keep the environment clean, follow capacity guidelines, and behave appropriately while
                                visiting.
                            </p>
                        </section>
                    </div>
                @else
                    {{-- Indonesian Content (Default) --}}
                    <div class="rounded-2xl border border-amber-100 bg-amber-50/50 p-4 text-xs text-amber-800">
                        <strong>Penting:</strong> Harap baca Syarat & Ketentuan ini dengan saksama sebelum menggunakan
                        aplikasi
                        Penglipuran Smart Tour. Dengan mendaftarkan akun, Anda setuju untuk mematuhi semua ketentuan yang
                        tercantum di sini.
                    </div>

                    <div class="space-y-4">
                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">1</span>
                                Pendaftaran Akun
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Untuk menggunakan fitur pemesanan tiket dan jelajah interaktif, Anda wajib mendaftarkan akun
                                dengan data yang valid. Anda bertanggung jawab penuh atas kerahasiaan kata sandi dan semua
                                aktivitas di bawah akun Anda.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">2</span>
                                Pemesanan Tiket & Paket Wisata
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Seluruh pemesanan dan transaksi melalui aplikasi ini bersifat final. E-ticket akan
                                diterbitkan
                                secara otomatis setelah verifikasi pembayaran berhasil dan wajib ditunjukkan di gerbang
                                masuk
                                desa untuk dipindai (QR Scan).
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">3</span>
                                Fitur Pemindaian AR & Lokasi
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Fitur Augmented Reality (AR) memerlukan izin akses kamera dan lokasi (GPS) untuk
                                memproyeksikan
                                konten edukasi budaya secara interaktif di titik koordinat desa. Data lokasi diproses secara
                                lokal demi kenyamanan jelajah Anda.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">4</span>
                                Peta Luring & Data Cache
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Aplikasi ini menyediakan fitur jelajah tanpa internet (offline mode) yang menyimpan data
                                tiket
                                masuk, peta dasar, dan buku saku secara luring. Pastikan Anda tidak menghapus cache browser
                                saat
                                melakukan perjalanan luring.
                            </p>
                        </section>

                        <section
                            class="hover:border-primary/20 group -mx-4 rounded-2xl border border-transparent p-4 transition-all duration-200">
                            <h3
                                class="text-charcoal group-hover:text-primary flex items-center gap-2 text-base font-bold transition-colors">
                                <span
                                    class="bg-primary/10 text-primary flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-xs font-bold">5</span>
                                Penghormatan Nilai Adat & Budaya
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Desa Penglipuran adalah desa adat yang suci. Pengguna berkomitmen menjaga kebersihan
                                lingkungan
                                desa, menghormati aturan adat setempat, mematuhi batas kapasitas zona, serta berperilaku
                                sopan
                                selama berkunjung.
                            </p>
                        </section>
                    </div>
                @endif


            </div>
        </div>
    </div>
@endsection
