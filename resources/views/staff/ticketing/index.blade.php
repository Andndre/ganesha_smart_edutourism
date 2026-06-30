@extends('layouts.dashboard')

@section('title', 'Layanan Tiket (POS)')

@push('styles')
    <style>
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@section('content')
    <div x-data='ticketingApp({ reservationsList: @json($reservationsList) })' class="max-w-6xl pb-24 sm:pb-0">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div id="tour-header" class="flex items-center gap-2">
                <div>
                    <h1 class="font-display text-charcoal text-2xl font-bold">Ticketing Point of Sale</h1>
                    <p class="mt-0.5 text-sm text-gray-500">Layanan pembelian tiket walk-in dan verifikasi pengunjung.</p>
                </div>
                <button id="tour-trigger-btn" onclick="startTutorial()"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition-all hover:bg-gray-100 active:scale-[0.98]"
                    title="Panduan Interaktif">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </button>
            </div>
            <div class="hidden flex-wrap justify-start gap-2.5 sm:flex lg:justify-end">
                <button id="tour-walkin-btn" type="button" @click="$dispatch('open-walkin-modal')"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition-all active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Beli Tiket Walk-in
                </button>
                <a id="tour-scanner-link" href="{{ route('staff.ticketing.scan') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-200 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Buka Scanner QR
                </a>
                <a id="tour-stats-link" href="{{ route('staff.ticketing.stats') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-200 active:scale-[0.98]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    Statistik Tiket
                </a>
            </div>
        </div>

        <!-- Tabel Transaksi Hari Ini -->
        <div id="tour-transactions-list" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-display text-charcoal text-lg font-bold">Tiket Terjual Hari Ini</h3>

                <!-- Filters & Sorting (ui-ux-pro-max) -->
                <div id="tour-filters" class="no-scrollbar flex w-full gap-2 overflow-x-auto pb-2 sm:flex-wrap sm:justify-end sm:pb-0">
                    <select x-model="filterStatus"
                        class="focus:border-primary min-w-32.5 shrink-0 rounded-xl border border-gray-100 bg-gray-50/50 px-2.5 py-2 text-xs text-gray-600 shadow-sm transition-all focus:bg-white focus:outline-none sm:w-auto">
                        <option value="all">Semua Status</option>
                        <option value="completed">Selesai</option>
                        <option value="confirmed">Menunggu</option>
                        <option value="pending">Pending</option>
                        <option value="cancelled">Batal</option>
                    </select>

                    <select x-model="filterPayment"
                        class="focus:border-primary min-w-33.75 shrink-0 rounded-xl border border-gray-100 bg-gray-50/50 px-2.5 py-2 text-xs text-gray-600 shadow-sm transition-all focus:bg-white focus:outline-none sm:w-auto">
                        <option value="all">Semua Metode</option>
                        <option value="cash">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>

                    <select x-model="sortBy"
                        class="focus:border-primary min-w-31.25 shrink-0 rounded-xl border border-gray-100 bg-gray-50/50 px-2.5 py-2 text-xs text-gray-600 shadow-sm transition-all focus:bg-white focus:outline-none sm:w-auto">
                        <option value="time_desc">Urut: Terbaru</option>
                        <option value="time_asc">Urut: Terlama</option>
                        <option value="amount_desc">Urut: Terbesar</option>
                        <option value="amount_asc">Urut: Terkecil</option>
                    </select>
                </div>
            </div>

            @include('staff.ticketing.partials.desktop-table')

            @include('staff.ticketing.partials.mobile-cards')
        </div>

        @include('staff.ticketing.partials.walkin-modal')

        <!-- Mobile Fixed Bottom Action Bar -->
        <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-100 bg-white/80 p-4 backdrop-blur-md sm:hidden"
            style="padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));">
            <div class="flex gap-2">
                <button type="button" @click="$dispatch('open-walkin-modal')"
                    class="bg-primary shadow-primary/20 inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl py-3 text-xs font-bold text-white shadow-lg transition-all active:scale-[0.97]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Beli Tiket
                </button>

                <a href="{{ route('staff.ticketing.scan') }}"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-gray-100 py-3 text-xs font-semibold text-gray-700 transition-all hover:bg-gray-200 active:scale-[0.97]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Scanner
                </a>

                <a href="{{ route('staff.ticketing.stats') }}"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-gray-100 py-3 text-xs font-semibold text-gray-700 transition-all hover:bg-gray-200 active:scale-[0.97]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2" />
                    </svg>
                    Statistik
                </a>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @include('staff.ticketing.partials.scripts')
@endpush

@push('scripts')
    <script>
        function startTutorial() {
            const driver = window.driver.js.driver;
            const steps = [];

            // Langkah 1: Pengantar
            steps.push({
                element: '#tour-header',
                popover: {
                    title: '👋 Selamat Datang di Loket Tiket!',
                    description: 'Panduan ini akan menunjukkan cara menjual tiket walk-in, memindai QR tiket, dan memantau transaksi hari ini.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 2: Tombol Beli Tiket Walk-in
            steps.push({
                element: '#tour-walkin-btn',
                popover: {
                    title: '🎫 Jual Tiket Walk-in',
                    description: 'Gunakan tombol ini untuk membuat tiket bagi pengunjung yang datang langsung tanpa pemesanan online. Isi data pengunjung, pilih paket, lalu proses pembayarannya.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 3: Scanner QR
            steps.push({
                element: '#tour-scanner-link',
                popover: {
                    title: '📷 Pindai QR Tiket',
                    description: 'Klik di sini untuk membuka kamera pemindai QR dan memverifikasi tiket pengunjung saat mereka check-in di lokasi.',
                    side: 'bottom',
                    align: 'start'
                }
            });

            // Langkah 4: Statistik Tiket
            steps.push({
                element: '#tour-stats-link',
                popover: {
                    title: '📊 Statistik Tiket',
                    description: 'Lihat ringkasan jumlah tiket terjual, pendapatan, dan data kunjungan lainnya di halaman statistik.',
                    side: 'bottom',
                    align: 'end'
                }
            });

            // Langkah 5: Filter & Sortir
            steps.push({
                element: '#tour-filters',
                popover: {
                    title: '🔍 Filter & Urutkan',
                    description: 'Gunakan filter ini untuk menyaring transaksi berdasarkan status atau metode pembayaran, dan mengurutkannya sesuai kebutuhan.',
                    side: 'top',
                    align: 'end'
                }
            });

            // Langkah 6: Daftar Transaksi & Check-in
            steps.push({
                element: '#tour-transactions-list',
                popover: {
                    title: '✅ Daftar Transaksi & Check-in',
                    description: 'Semua tiket yang terjual hari ini muncul di sini. Gunakan tombol "Check In" untuk pengunjung yang sudah tiba, atau "Bayar"/"Batal" untuk transaksi yang masih pending.',
                    side: 'top',
                    align: 'start'
                }
            });

            const driverObj = driver({
                showProgress: true,
                allowClose: true,
                steps: steps,
                popoverClass: 'driverjs-theme'
            });

            driverObj.drive();
        }

        // Auto-run for first-time visitors
        document.addEventListener('DOMContentLoaded', () => {
            const tourCompleted = localStorage.getItem('staff_ticketing_tour_completed');
            if (!tourCompleted) {
                setTimeout(() => {
                    startTutorial();
                    localStorage.setItem('staff_ticketing_tour_completed', 'true');
                }, 1000);
            }
        });
    </script>
@endpush
