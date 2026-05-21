@extends('layouts.app')
@section('title', 'Profil & Tiket Saya')
@section('header_title', 'Profil Saya')

@section('content')
    <div class="px-4 pb-24 pt-[calc(env(safe-area-inset-top)+6rem)]">

        <!-- User Info Card -->
        <div class="mb-6 flex items-center gap-4 rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
            <div
                class="relative flex h-16 w-16 items-center justify-center overflow-hidden rounded-full border-2 border-white bg-gray-200 text-gray-400 shadow-md">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div>
                <h2 class="text-charcoal text-xl font-bold">Andre Kusuma</h2>
                <p class="text-sm text-gray-500">andre@test.com</p>
                <div class="mt-1 flex items-center gap-1">
                    <span class="h-2 w-2 rounded-full bg-green-500"></span>
                    <span class="text-xs font-semibold text-green-600">Akun Terverifikasi</span>
                </div>
            </div>
        </div>

        <h3 class="text-charcoal mb-4 text-lg font-bold">Tiket Aktif Saya</h3>

        <!-- Active Ticket Card -->
        <div class="bg-primary shadow-primary/20 mb-8 rounded-3xl p-1 shadow-lg transition-transform active:scale-[0.98]"
            onclick="openQrModal()">
            <div class="border-primary/20 relative overflow-hidden rounded-[1.35rem] border bg-white">

                <!-- Ticket Top (Details) -->
                <div class="relative z-10 border-b-2 border-dashed border-gray-200 p-5">
                    <div class="mb-3 flex items-start justify-between">
                        <span
                            class="text-primary rounded-lg border border-green-100 bg-green-50 px-2.5 py-1 text-xs font-bold">Tour
                            Keluarga</span>
                        <span class="text-xs font-bold text-gray-400">ID: GPN-2026815</span>
                    </div>
                    <h3 class="text-charcoal mb-4 text-lg font-bold">Penglipuran Family Walk</h3>

                    <div class="grid grid-cols-2 gap-x-2 gap-y-4">
                        <div>
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Tanggal</div>
                            <div class="text-charcoal text-sm font-bold">15 Agu 2026</div>
                        </div>
                        <div>
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Jam</div>
                            <div class="text-charcoal text-sm font-bold">09:00 WITA</div>
                        </div>
                        <div>
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Peserta</div>
                            <div class="text-charcoal text-sm font-bold">3 Orang</div>
                        </div>
                        <div>
                            <div class="mb-1 text-[10px] font-bold uppercase tracking-wider text-gray-400">Status</div>
                            <div class="text-sm font-bold text-green-600">Lunas</div>
                        </div>
                    </div>
                </div>

                <!-- Ticket Bottom (CTA) -->
                <div class="flex items-center justify-between bg-gray-50 p-5">
                    <div class="flex items-center gap-3">
                        <div
                            class="text-charcoal flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-white shadow-sm">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-charcoal text-sm font-bold">Ketuk untuk QR Code</div>
                            <div class="text-[10px] text-gray-500">Tunjukkan di pintu masuk</div>
                        </div>
                    </div>
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>

                <!-- Left and Right cutouts (Ticket effect) -->
                <div class="absolute -left-3 top-[67%] h-6 w-6 rounded-full border border-gray-200 bg-gray-50"></div>
                <div class="absolute -right-3 top-[67%] h-6 w-6 rounded-full border border-gray-200 bg-gray-50"></div>
            </div>
        </div>

        <!-- Other Menu Options -->
        <h3 class="text-charcoal mb-4 text-lg font-bold">Pengaturan</h3>
        <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
            <a href="#" class="flex items-center justify-between border-b border-gray-50 p-4 active:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="text-charcoal text-sm font-medium">Riwayat Pemesanan</span>
                </div>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <a href="{{ route('feedback') }}"
                class="flex items-center justify-between border-b border-gray-50 p-4 active:bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="text-accent flex h-8 w-8 items-center justify-center rounded-full bg-amber-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <span class="text-charcoal text-sm font-medium">Beri Penilaian & Ulasan</span>
                </div>
                <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>

            <div class="p-4">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 text-red-500">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-50">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-red-500">Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- QR Code Modal (Hidden by default) -->
    <div id="qr-modal"
        class="bg-charcoal/90 z-60 pointer-events-none fixed inset-0 flex items-center justify-center p-6 opacity-0 backdrop-blur-sm transition-opacity duration-300">
        <div class="rounded-4xl w-full max-w-sm scale-95 transform overflow-hidden bg-white shadow-2xl transition-transform duration-300"
            id="qr-card">

            <div class="relative p-6 pb-2 text-center">
                <button onclick="closeQrModal()"
                    class="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 active:bg-gray-200">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <h3 class="text-charcoal mb-1 text-xl font-bold">Tiket Masuk</h3>
                <p class="text-xs text-gray-500">Pindai QR ini di gerbang utama</p>
            </div>

            <div class="flex justify-center p-8">
                <!-- Simulated QR Code -->
                <div
                    class="relative aspect-square w-full rounded-2xl border-8 border-white bg-white shadow-[0_0_15px_rgba(0,0,0,0.1)]">
                    <!-- Outer squares -->
                    <div class="border-charcoal absolute left-2 top-2 h-12 w-12 border-4"></div>
                    <div class="bg-charcoal absolute left-4 top-4 h-8 w-8"></div>

                    <div class="border-charcoal absolute right-2 top-2 h-12 w-12 border-4"></div>
                    <div class="bg-charcoal absolute right-4 top-4 h-8 w-8"></div>

                    <div class="border-charcoal absolute bottom-2 left-2 h-12 w-12 border-4"></div>
                    <div class="bg-charcoal absolute bottom-4 left-4 h-8 w-8"></div>

                    <!-- Random QR blocks (simulated) -->
                    <div class="bg-charcoal absolute left-[30%] top-[40%] h-6 w-6"></div>
                    <div class="bg-charcoal absolute right-[30%] top-[60%] h-4 w-8"></div>
                    <div class="bg-charcoal absolute bottom-[20%] right-[20%] h-10 w-10"></div>
                    <div class="bg-charcoal absolute left-[50%] top-[20%] h-12 w-4"></div>

                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-md">
                            <span class="text-primary text-[10px] font-bold">GPN</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 bg-gray-50 p-6 text-center">
                <div class="mb-1 text-xs font-bold uppercase tracking-wider text-gray-500">ID Pemesanan</div>
                <div class="text-charcoal font-mono text-lg font-bold tracking-[0.2em]">GPN-2026815</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openQrModal() {
            const modal = document.getElementById('qr-modal');
            const card = document.getElementById('qr-card');

            modal.classList.remove('opacity-0', 'pointer-events-none');
            modal.classList.add('opacity-100', 'pointer-events-auto');

            card.classList.remove('scale-95');
            card.classList.add('scale-100');

            // Maximize screen brightness hack (simulated via haptic)
            if (navigator.vibrate) navigator.vibrate(50);
        }

        function closeQrModal() {
            const modal = document.getElementById('qr-modal');
            const card = document.getElementById('qr-card');

            modal.classList.remove('opacity-100', 'pointer-events-auto');
            modal.classList.add('opacity-0', 'pointer-events-none');

            card.classList.remove('scale-100');
            card.classList.add('scale-95');
        }
    </script>
@endpush
