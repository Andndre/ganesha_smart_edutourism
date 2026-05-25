@extends('layouts.auth')

@section('title', __('Syarat & Ketentuan') . ' - Penglipuran Smart Tour')

@section('content')
    <div class="bg-surface min-h-full pb-12">
        {{-- Custom Page Header --}}
        <div class="bg-primary pt-sat sticky top-0 z-30 shadow-md text-white">
            <div class="mx-auto flex h-16 max-w-2xl items-center justify-between px-4">
                {{-- Back button --}}
                <a href="javascript:void(0)"
                    onclick="if (document.referrer && document.referrer.includes(window.location.host)) { history.back(); } else { window.location.href = '{{ route('register') }}'; }"
                    class="tap-target -ml-2 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition-all active:scale-95"
                    aria-label="{{ __('Kembali') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>

                {{-- Page Title --}}
                <h1 class="text-base font-bold tracking-wide">
                    {{ __('Syarat & Ketentuan') }}
                </h1>

                {{-- Language Switcher Dropdown/Buttons --}}
                <div class="relative flex items-center gap-1.5">
                    <a href="{{ route('terms', ['locale' => 'id']) }}" 
                       class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ app()->getLocale() === 'id' ? 'bg-white text-primary shadow-sm' : 'bg-white/10 text-white hover:bg-white/20' }}">
                        ID
                    </a>
                    <a href="{{ route('terms', ['locale' => 'en']) }}" 
                       class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all {{ app()->getLocale() === 'en' ? 'bg-white text-primary shadow-sm' : 'bg-white/10 text-white hover:bg-white/20' }}">
                        EN
                    </a>
                </div>
            </div>
        </div>

        {{-- Document Header --}}
        <div class="mx-auto max-w-2xl px-4 pt-8 text-center">
            <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
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
            <div class="bg-white rounded-3xl p-6 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] border border-gray-100 space-y-6 text-sm leading-relaxed text-gray-600">
                
                @if(app()->getLocale() === 'en')
                    {{-- English Content --}}
                    <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100 text-amber-800 text-xs">
                        <strong>Important:</strong> Please read these Terms & Conditions carefully before using the Penglipuran Smart Tour application. By registering an account, you agree to comply with all terms specified herein.
                    </div>

                    <div class="space-y-4">
                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">1</span>
                                Account Registration
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                To use the booking and interactive tourism features, you must register a valid account. You are responsible for maintaining the confidentiality of your password and all activities under your account.
                            </p>
                        </section>

                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">2</span>
                                Ticket Booking & Tour Packages
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                All bookings and transactions made through this application are final. E-tickets will be issued immediately upon verification of payment and must be shown at the village entry gate for QR scanning.
                            </p>
                        </section>

                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">3</span>
                                AR Scan & Location Permissions
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                The Augmented Reality (AR) feature requires camera and geolocation access to project interactive, culture-rich content within the village coordinates. Geolocation data is only used locally to load spatial landmarks.
                            </p>
                        </section>

                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">4</span>
                                Offline Maps & Caching
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                This app offers robust offline functionalities, saving essential travel tickets, maps, and cultural documents locally in your browser cache. Do not clear your browser cache while exploring offline to prevent data loss.
                            </p>
                        </section>

                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">5</span>
                                Respect Cultural Heritage
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Penglipuran is a sacred traditional village. Users agree to respect local Balinese traditions, keep the environment clean, follow capacity guidelines, and behave appropriately while visiting.
                            </p>
                        </section>
                    </div>
                @else
                    {{-- Indonesian Content (Default) --}}
                    <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100 text-amber-800 text-xs">
                        <strong>Penting:</strong> Harap baca Syarat & Ketentuan ini dengan saksama sebelum menggunakan aplikasi Penglipuran Smart Tour. Dengan mendaftarkan akun, Anda setuju untuk mematuhi semua ketentuan yang tercantum di sini.
                    </div>

                    <div class="space-y-4">
                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">1</span>
                                Pendaftaran Akun
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Untuk menggunakan fitur pemesanan tiket dan jelajah interaktif, Anda wajib mendaftarkan akun dengan data yang valid. Anda bertanggung jawab penuh atas kerahasiaan kata sandi dan semua aktivitas di bawah akun Anda.
                            </p>
                        </section>

                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">2</span>
                                Pemesanan Tiket & Paket Wisata
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Seluruh pemesanan dan transaksi melalui aplikasi ini bersifat final. E-ticket akan diterbitkan secara otomatis setelah verifikasi pembayaran berhasil dan wajib ditunjukkan di gerbang masuk desa untuk dipindai (QR Scan).
                            </p>
                        </section>

                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">3</span>
                                Fitur Pemindaian AR & Lokasi
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Fitur Augmented Reality (AR) memerlukan izin akses kamera dan lokasi (GPS) untuk memproyeksikan konten edukasi budaya secara interaktif di titik koordinat desa. Data lokasi diproses secara lokal demi kenyamanan jelajah Anda.
                            </p>
                        </section>

                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">4</span>
                                Peta Luring & Data Cache
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Aplikasi ini menyediakan fitur jelajah tanpa internet (offline mode) yang menyimpan data tiket masuk, peta dasar, dan buku saku secara luring. Pastikan Anda tidak menghapus cache browser saat melakukan perjalanan luring.
                            </p>
                        </section>

                        <section class="group hover:border-primary/20 p-4 -mx-4 rounded-2xl border border-transparent transition-all duration-200">
                            <h3 class="text-base font-bold text-charcoal flex items-center gap-2 group-hover:text-primary transition-colors">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary">5</span>
                                Penghormatan Nilai Adat & Budaya
                            </h3>
                            <p class="mt-2 pl-8 text-gray-500">
                                Desa Penglipuran adalah desa adat yang suci. Pengguna berkomitmen menjaga kebersihan lingkungan desa, menghormati aturan adat setempat, mematuhi batas kapasitas zona, serta berperilaku sopan selama berkunjung.
                            </p>
                        </section>
                    </div>
                @endif

                {{-- Action Footer --}}
                <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row gap-3">
                    <a href="javascript:void(0)"
                       onclick="if (document.referrer && document.referrer.includes(window.location.host)) { history.back(); } else { window.location.href = '{{ route('register') }}'; }"
                       class="tap-target w-full sm:flex-1 py-3 text-center rounded-xl bg-primary hover:bg-primary-600 text-white font-bold transition-all shadow-md active:scale-95">
                        {{ __('Kembali ke Pendaftaran') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
