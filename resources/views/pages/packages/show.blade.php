@extends('layouts.app')
@section('title', 'Detail Paket - Penglipuran')
@section('header_title', 'Reservasi Paket')

@section('content')
    <div class="relative pb-32">
        <!-- Image Header -->
        <div class="relative aspect-video w-full bg-gray-200">
            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <!-- Back Button Overlay (if not using header, but we are using header) -->
        </div>

        <!-- Package Details -->
        <div class="relative z-10 -mt-6 rounded-t-3xl border-b border-gray-100 bg-white px-5 py-6 shadow-sm">
            <div class="mb-2">
                <span
                    class="text-primary mb-3 inline-block rounded-lg border border-green-100 bg-green-50 px-2.5 py-1 text-xs font-bold">Paket
                    Keluarga</span>
                <h1 class="text-charcoal text-xl font-bold">Penglipuran Family Walk</h1>
            </div>

            <p class="mb-4 text-sm leading-relaxed text-gray-500">
                Tour keliling desa wisata yang ramah anak, dilengkapi dengan sesi belajar membuat kerajinan janur Bali
                bersama masyarakat lokal. Sempurna untuk liburan akhir pekan.
            </p>

            <div class="mb-2 grid grid-cols-2 gap-3">
                <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 p-3">
                    <svg class="text-accent mb-1 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xs font-medium text-gray-500">Durasi</span>
                    <span class="text-charcoal text-sm font-bold">2 Jam</span>
                </div>
                <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 p-3">
                    <svg class="text-accent mb-1 h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs font-medium text-gray-500">Fasilitas</span>
                    <span class="text-charcoal text-sm font-bold">Guide, Janur</span>
                </div>
            </div>
        </div>

        <!-- Reservation Form -->
        <div class="mt-2 border-y border-gray-100 bg-white px-5 py-6">
            <h3 class="text-charcoal mb-4 font-bold">Pilih Tanggal Tour</h3>

            <!-- Date Scroller (Horizontal Chips) -->
            <div class="no-scrollbar -mx-5 flex gap-3 overflow-x-auto px-5 pb-2">
                <!-- Active Date -->
                <label
                    class="border-primary min-w-17.5 relative flex shrink-0 cursor-pointer flex-col items-center rounded-2xl border-2 bg-green-50 p-3">
                    <input type="radio" name="tour_date" value="today" class="absolute opacity-0" checked>
                    <span class="text-primary mb-1 text-xs font-bold">Hari Ini</span>
                    <span class="text-charcoal text-xl font-bold">15</span>
                    <span class="text-xs font-medium text-gray-500">Agu</span>
                </label>

                <!-- Inactive Date 1 -->
                <label
                    class="min-w-17.5 relative flex shrink-0 cursor-pointer flex-col items-center rounded-2xl border border-gray-200 bg-white p-3 transition-colors hover:border-gray-300">
                    <input type="radio" name="tour_date" value="tomorrow" class="absolute opacity-0">
                    <span class="mb-1 text-xs font-semibold text-gray-400">Besok</span>
                    <span class="text-charcoal text-xl font-bold">16</span>
                    <span class="text-xs font-medium text-gray-500">Agu</span>
                </label>

                <!-- Inactive Date 2 -->
                <label
                    class="min-w-17.5 relative flex shrink-0 cursor-pointer flex-col items-center rounded-2xl border border-gray-200 bg-white p-3 transition-colors hover:border-gray-300">
                    <input type="radio" name="tour_date" value="next1" class="absolute opacity-0">
                    <span class="mb-1 text-xs font-semibold text-gray-400">Senin</span>
                    <span class="text-charcoal text-xl font-bold">17</span>
                    <span class="text-xs font-medium text-gray-500">Agu</span>
                </label>

                <!-- Custom Date Selector -->
                <label
                    class="min-w-17.5 relative flex shrink-0 cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-3">
                    <svg class="mb-1 h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-xs font-semibold text-gray-500">Lainnya</span>
                </label>
            </div>

            <!-- Party Size Stepper -->
            <h3 class="text-charcoal mb-4 mt-6 font-bold">Jumlah Peserta (Party Size)</h3>
            <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50 p-4">
                <div>
                    <div class="text-charcoal font-bold">Peserta Dewasa / Anak</div>
                    <div class="mt-0.5 text-xs text-gray-500">Minimal 3 Orang</div>
                </div>

                <div class="flex items-center gap-4 rounded-full border border-gray-200 bg-white px-2 py-1.5 shadow-sm">
                    <button
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-500 active:bg-gray-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                    </button>
                    <span class="text-charcoal w-4 text-center text-lg font-bold">3</span>
                    <button
                        class="bg-primary/10 text-primary active:bg-primary/20 flex h-8 w-8 items-center justify-center rounded-full">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Contact Info -->
            <h3 class="text-charcoal mb-4 mt-6 font-bold">Informasi Kontak Pemesan</h3>
            <div class="space-y-3">
                <input type="text" placeholder="Nama Lengkap"
                    class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
                <input type="email" placeholder="Alamat Email (Untuk E-Ticket)"
                    class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
                <input type="tel" placeholder="Nomor WhatsApp aktif"
                    class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
            </div>
        </div>
    </div>

    <!-- Sticky Bottom Checkout Action (Posisi di atas Bottom Nav) -->
    <div
        class="fixed inset-x-0 bottom-[calc(4rem+env(safe-area-inset-bottom))] z-30 border-t border-gray-100 bg-white p-4 shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
        <div class="mb-3 flex items-center justify-between px-1">
            <span class="text-sm font-medium text-gray-500">Total Harga (3 Pax)</span>
            <span class="text-primary text-lg font-bold">Rp 450.000</span>
        </div>
        <button
            class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98]"
            onclick="if(navigator.vibrate) navigator.vibrate(50)">
            Lanjutkan ke Pembayaran
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </button>
    </div>
@endsection
