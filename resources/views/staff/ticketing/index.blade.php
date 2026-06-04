@extends('layouts.dashboard')

@section('title', 'Layanan Tiket (POS)')

@section('content')
<div x-data='ticketingApp({ reservationsList: @json($reservationsList) })' class="max-w-6xl pb-24 sm:pb-0">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-display text-2xl font-bold text-charcoal">Ticketing Point of Sale</h1>
            <p class="mt-0.5 text-sm text-gray-500">Layanan pembelian tiket walk-in dan verifikasi pengunjung.</p>
        </div>
        <div class="hidden sm:flex flex-wrap gap-3">
            <button type="button" @click="$dispatch('open-walkin-modal')" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Beli Tiket Walk-in
            </button>
            <a href="{{ route('staff.ticketing.scan') }}" class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition-all active:scale-[0.98]">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Buka Scanner QR
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-xl bg-primary/10 border border-primary/20 p-4 text-sm text-primary">
            <svg class="h-5 w-5 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Statistics Cards Grid (ui-ux-pro-max) -->
    <div class="mb-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1 -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tiket Terjual</p>
                <h4 class="text-xl font-bold text-charcoal mt-0.5">{{ $totalTicketsSold }} <span class="text-xs font-medium text-gray-500">Tiket</span></h4>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16v1m0-2h.01" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pendapatan</p>
                <h4 class="text-lg font-bold text-charcoal mt-0.5">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tunai (Cash)</p>
                <h4 class="text-lg font-bold text-charcoal mt-0.5">Rp {{ number_format($cashRevenue, 0, ',', '.') }}</h4>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M4 8h.01M4 16h.01M4 20h.01m1.99-16h.01M12 4h.01M16 4h.01" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">QRIS</p>
                <h4 class="text-lg font-bold text-charcoal mt-0.5">Rp {{ number_format($qrisRevenue, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi Hari Ini -->
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="font-display text-lg font-bold text-charcoal">Tiket Terjual Hari Ini</h3>
            
            <!-- Filters & Sorting (ui-ux-pro-max) -->
            <div class="grid grid-cols-3 gap-2 w-full sm:flex sm:w-auto sm:items-center sm:gap-3">
                <select x-model="filterStatus" class="w-full rounded-xl border border-gray-100 bg-gray-50/50 px-2.5 py-2 text-xs text-gray-600 focus:border-primary focus:bg-white focus:outline-none shadow-sm transition-all sm:w-auto">
                    <option value="all">Semua Status</option>
                    <option value="completed">Selesai</option>
                    <option value="confirmed">Menunggu</option>
                    <option value="pending">Pending</option>
                    <option value="cancelled">Batal</option>
                </select>
                
                <select x-model="filterPayment" class="w-full rounded-xl border border-gray-100 bg-gray-50/50 px-2.5 py-2 text-xs text-gray-600 focus:border-primary focus:bg-white focus:outline-none shadow-sm transition-all sm:w-auto">
                    <option value="all">Semua Metode</option>
                    <option value="cash">Tunai</option>
                    <option value="qris">QRIS</option>
                </select>

                <select x-model="sortBy" class="w-full rounded-xl border border-gray-100 bg-gray-50/50 px-2.5 py-2 text-xs text-gray-600 focus:border-primary focus:bg-white focus:outline-none shadow-sm transition-all sm:w-auto">
                    <option value="time_desc">Terbaru</option>
                    <option value="time_asc">Terlama</option>
                    <option value="amount_desc">Terbesar</option>
                    <option value="amount_asc">Terkecil</option>
                </select>
            </div>
        </div>

        <!-- Desktop Table (Hidden on Mobile) -->
        <div class="hidden sm:block overflow-x-auto rounded-xl border border-gray-100">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Pembeli</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Paket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Jumlah & Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="res in filteredAndSortedReservations()" :key="res.id">
                        <tr class="hover:bg-gray-50/30">
                            <td class="px-4 py-3.5 font-semibold text-charcoal">
                                <span x-text="res.guest_name"></span>
                                <template x-if="res.is_walkin">
                                    <span class="ml-1 inline-flex items-center rounded bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">Walk-in</span>
                                </template>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600" x-text="res.package_name"></td>
                            <td class="px-4 py-3.5 text-gray-600">
                                <span x-text="res.party_size + ' Org'"></span> <br>
                                <span class="text-xs font-semibold text-charcoal" x-text="formatRupiah(res.total_amount)"></span>
                            </td>
                            <td class="px-4 py-3.5">
                                <template x-if="res.status === 'completed'">
                                    <span class="inline-flex rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">Selesai/Masuk</span>
                                </template>
                                <template x-if="res.status === 'confirmed'">
                                    <span class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
                                </template>
                                <template x-if="res.status !== 'completed' && res.status !== 'confirmed'">
                                    <span class="inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600" x-text="capitalize(res.status)"></span>
                                </template>
                            </td>
                            <td class="px-4 py-3.5 text-gray-400 text-xs" x-text="res.time"></td>
                            <td class="px-4 py-3.5">
                                <div class="flex gap-1.5">
                                    <template x-if="res.status === 'confirmed'">
                                        <button @click="checkInReservation(res.id)" class="inline-flex items-center rounded-lg bg-primary px-2.5 py-1 text-xs font-bold text-white hover:bg-primary-600 transition-all shadow-sm">
                                            Check In
                                        </button>
                                    </template>
                                    <template x-if="res.status === 'pending'">
                                        <div class="flex gap-1.5">
                                            <template x-if="res.payment_method === 'qris'">
                                                <div class="flex gap-1.5">
                                                    <button @click="payQRIS(res.id)" class="inline-flex items-center rounded-lg bg-amber-500 px-2.5 py-1 text-xs font-bold text-white hover:bg-amber-600 transition-all shadow-sm">
                                                        Bayar
                                                    </button>
                                                    <button @click="syncReservation(res.id)" class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700 hover:bg-gray-200 transition-all" title="Sync Status">
                                                        Sync
                                                    </button>
                                                </div>
                                            </template>
                                            <button @click="cancelReservation(res.id)" class="inline-flex items-center rounded-lg bg-red-50 px-2 py-1 text-xs font-bold text-red-700 hover:bg-red-100 transition-all" title="Batalkan">
                                                Batal
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredAndSortedReservations().length === 0">
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada transaksi yang cocok dengan filter.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Mobile Card List (Visible only on Mobile) -->
        <div class="space-y-4 sm:hidden">
            <template x-for="res in filteredAndSortedReservations()" :key="'mob-' + res.id">
                <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <h4 class="font-bold text-charcoal text-sm" x-text="res.guest_name"></h4>
                            <div class="mt-0.5 flex items-center gap-1.5">
                                <span class="text-[10px] text-gray-400" x-text="'Pukul ' + res.time"></span>
                                <template x-if="res.is_walkin">
                                    <span class="inline-flex items-center rounded bg-primary/10 px-1.5 py-0.2 text-[8px] font-semibold text-primary">Walk-in</span>
                                </template>
                            </div>
                        </div>
                        <div>
                            <template x-if="res.status === 'completed'">
                                <span class="inline-flex rounded-lg bg-primary/10 px-2.5 py-1 text-[10px] font-semibold text-primary">Selesai</span>
                            </template>
                            <template x-if="res.status === 'confirmed'">
                                <span class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700">Menunggu</span>
                            </template>
                            <template x-if="res.status !== 'completed' && res.status !== 'confirmed'">
                                <span class="inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-[10px] font-semibold text-gray-600" x-text="capitalize(res.status)"></span>
                            </template>
                        </div>
                    </div>

                    <div class="rounded-xl bg-gray-50/55 p-3 text-xs space-y-1.5">
                        <div class="flex justify-between text-gray-500">
                            <span>Paket Wisata</span>
                            <span class="font-semibold text-charcoal text-right max-w-[150px] truncate" x-text="res.package_name"></span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Jumlah Peserta</span>
                            <span class="font-semibold text-charcoal" x-text="res.party_size + ' Orang'"></span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Total Pembayaran</span>
                            <span class="font-bold text-primary" x-text="formatRupiah(res.total_amount)"></span>
                        </div>
                    </div>

                    <!-- Actions for Mobile -->
                    <template x-if="res.status === 'confirmed' || res.status === 'pending'">
                        <div class="flex gap-2 pt-1">
                            <template x-if="res.status === 'confirmed'">
                                <button @click="checkInReservation(res.id)" class="w-full text-center rounded-xl bg-primary py-2.5 text-xs font-bold text-white shadow-md shadow-primary/15 active:scale-[0.98] transition-all">
                                    Check In / Masuk
                                </button>
                            </template>
                            <template x-if="res.status === 'pending'">
                                <div class="flex gap-2 w-full">
                                    <button x-show="res.payment_method === 'qris'" @click="payQRIS(res.id)" class="flex-1 text-center rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-white shadow-md shadow-amber-500/15 active:scale-[0.98] transition-all">
                                        Bayar
                                    </button>
                                    <button x-show="res.payment_method === 'qris'" @click="syncReservation(res.id)" class="flex-1 text-center rounded-xl bg-gray-100 py-2.5 text-xs font-semibold text-gray-700 active:scale-[0.98] transition-all">
                                        Sync
                                    </button>
                                    <button @click="cancelReservation(res.id)" class="flex-1 text-center rounded-xl bg-red-50 py-2.5 text-xs font-semibold text-red-700 active:scale-[0.98] transition-all">
                                        Batal
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
            <template x-if="filteredAndSortedReservations().length === 0">
                <div class="rounded-xl border border-dashed border-gray-200 py-8 text-center text-gray-400 text-sm">Tidak ada transaksi yang cocok dengan filter.</div>
            </template>
        </div>
    </div>

        <!-- Walk-in Purchase Modal -->
        <x-modal name="walkin-modal" maxWidth="md">
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-display text-lg font-bold text-charcoal">Pembelian Tiket Walk-in</h3>
                </div>
                
                <form id="walkin-form" action="{{ route('staff.ticketing.walk-in') }}" method="POST" class="mt-4">
                    @csrf

                    <div class="space-y-4 text-left">
                        <div>
                            <label for="guest_name" class="block text-sm font-semibold text-gray-700">Nama Pengunjung <span class="text-warning">*</span></label>
                            <input type="text" name="guest_name" id="guest_name" required placeholder="Nama lengkap pengunjung"
                                class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        </div>

                        <div>
                            <label for="guest_email" class="block text-sm font-semibold text-gray-700">Email (Opsional)</label>
                            <input type="email" name="guest_email" id="guest_email" placeholder="email@contoh.com"
                                class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                        </div>

                        <div>
                            <label for="tour_package_id" class="block text-sm font-semibold text-gray-700">Paket Wisata <span class="text-warning">*</span></label>
                            <select id="tour_package_id" name="tour_package_id" required
                                class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none bg-white">
                                <option value="">Pilih paket...</option>
                                @foreach ($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="party_size" class="block text-sm font-semibold text-gray-700">Jumlah Orang <span class="text-warning">*</span></label>
                                <input type="number" name="party_size" id="party_size" min="1" value="1" required
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none">
                            </div>
                            <div>
                                <label for="payment_method" class="block text-sm font-semibold text-gray-700">Metode Bayar <span class="text-warning">*</span></label>
                                <select id="payment_method" name="payment_method" required
                                    class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:border-primary focus:outline-none bg-white">
                                    <option value="cash">Tunai (Cash)</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 flex gap-3">
                            <button type="button" @click="isOpen = false" class="flex-1 justify-center rounded-xl bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-200 active:scale-[0.99] transition-all">
                                Batal
                            </button>
                            <button type="submit" style="flex: 2;"
                                class="justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600 active:scale-[0.99] transition-all">
                                Proses & Cetak Tiket
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </x-modal>

        <!-- Mobile Fixed Bottom Action Bar -->
        <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-100 bg-white/80 p-4 backdrop-blur-md sm:hidden" style="padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px));">
            <div class="flex gap-3">
                <button type="button" @click="$dispatch('open-walkin-modal')" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-primary py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all active:scale-[0.97]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Beli Tiket
                </button>
                
                <a href="{{ route('staff.ticketing.scan') }}" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-gray-100 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-200 transition-all active:scale-[0.97]">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Scanner QR
                </a>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @if(config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ticketingApp', (config) => ({
                reservations: config.reservationsList || [],
                filterStatus: 'all',
                filterPayment: 'all',
                sortBy: 'time_desc',
                filteredAndSortedReservations() {
                    return this.reservations
                        .filter(res => {
                            const statusMatch = this.filterStatus === 'all' || res.status === this.filterStatus;
                            const paymentMatch = this.filterPayment === 'all' || 
                                (this.filterPayment === 'cash' ? res.payment_method === 'cash' : res.payment_method !== 'cash');
                            return statusMatch && paymentMatch;
                        })
                        .sort((a, b) => {
                            if (this.sortBy === 'time_desc') return b.timestamp - a.timestamp;
                            if (this.sortBy === 'time_asc') return a.timestamp - b.timestamp;
                            if (this.sortBy === 'amount_desc') return b.total_amount - a.total_amount;
                            if (this.sortBy === 'amount_asc') return a.total_amount - b.total_amount;
                            return 0;
                        });
                },
                formatRupiah(amount) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                },
                capitalize(val) {
                    if (!val) return '';
                    return val.charAt(0).toUpperCase() + val.slice(1);
                }
            }));
        });

        async function checkInReservation(id) {
            const { isConfirmed } = await Swal.fire({
                title: 'Konfirmasi Masuk',
                text: 'Apakah Anda yakin ingin melakukan check-in untuk pengunjung ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1E5128',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Check-in!',
                cancelButtonText: 'Batal'
            });

            if (!isConfirmed) return;

            try {
                const response = await fetch(`/staff/ticketing/check-in/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire('Berhasil!', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Terjadi Kesalahan', 'Gagal memproses check-in.', 'error');
            }
        }

        async function syncReservation(id) {
            try {
                Swal.showLoading();
                const response = await fetch(`/staff/ticketing/sync/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                Swal.close();
                if (data.success) {
                    const statusText = data.status === 'completed' ? 'Sudah Dibayar' : 'Belum Dibayar';
                    const icon = data.status === 'completed' ? 'success' : 'info';
                    Swal.fire({
                        title: 'Sinkronisasi Selesai',
                        text: `Status tiket saat ini: ${statusText}`,
                        icon: icon,
                        confirmButtonColor: '#1E5128'
                    }).then(() => {
                        window.location.reload();
                    });
                }
            } catch (error) {
                Swal.close();
                console.error(error);
                Swal.fire('Gagal', 'Gagal sinkronisasi data dari Midtrans.', 'error');
            }
        }

        async function cancelReservation(id) {
            const { isConfirmed } = await Swal.fire({
                title: 'Batalkan Tiket',
                text: 'Apakah Anda yakin ingin membatalkan pesanan tiket ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Batalkan!',
                cancelButtonText: 'Kembali'
            });

            if (!isConfirmed) return;

            try {
                const response = await fetch(`/staff/ticketing/cancel/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire('Dibatalkan!', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Terjadi kesalahan.', 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Terjadi Kesalahan', 'Gagal membatalkan tiket.', 'error');
            }
        }

        async function payQRIS(id) {
            try {
                Swal.showLoading();
                const response = await fetch(`/staff/ticketing/pay/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await response.json();
                Swal.close();

                if (data.success && data.snap_token) {
                    snap.pay(data.snap_token, {
                        onSuccess: async function(result) {
                            try {
                                await fetch(`/staff/ticketing/sync/${id}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                });
                            } catch (e) {
                                console.error('Sync error:', e);
                            }
                            Swal.fire({
                                title: 'Pembayaran Berhasil!',
                                text: 'Tiket QRIS walk-in berhasil divalidasi.',
                                icon: 'success',
                                confirmButtonColor: '#1E5128',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        onPending: async function(result) {
                            try {
                                await fetch(`/staff/ticketing/sync/${id}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    }
                                });
                            } catch (e) {
                                console.error('Sync error:', e);
                            }
                            Swal.fire({
                                title: 'Menunggu Pembayaran',
                                text: 'Silakan selesaikan pembayaran QRIS pada aplikasi Anda.',
                                icon: 'info',
                                confirmButtonColor: '#1E5128',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        onError: function(result) {
                            Swal.fire({
                                title: 'Gagal',
                                text: 'Pembayaran QRIS gagal diproses.',
                                icon: 'error',
                                confirmButtonColor: '#1E5128'
                            });
                        },
                        onClose: function() {
                            Swal.fire({
                                title: 'Info',
                                text: 'Pop-up pembayaran QRIS ditutup.',
                                icon: 'info',
                                confirmButtonColor: '#1E5128'
                            });
                        }
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal mendapatkan token pembayaran.', 'error');
                }
            } catch (error) {
                Swal.close();
                console.error(error);
                Swal.fire('Terjadi Kesalahan', 'Gagal memproses pembayaran QRIS.', 'error');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('walkin-form');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    // Disable button and show spinner
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="animate-spin h-5 w-5 text-white mx-auto inline-block" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-2">Memproses...</span>
                    `;
                    
                    const formData = new FormData(form);
                    
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            if (data.payment_method === 'cash') {
                                window.dispatchEvent(new CustomEvent('close-walkin-modal'));
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonColor: '#1E5128',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else if (data.payment_method === 'qris' && data.snap_token) {
                                window.dispatchEvent(new CustomEvent('close-walkin-modal'));
                                snap.pay(data.snap_token, {
                                    onSuccess: async function(result) {
                                        try {
                                            await fetch(`/staff/ticketing/sync/${data.reservation_id}`, {
                                                method: 'POST',
                                                headers: {
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                }
                                            });
                                        } catch (e) {
                                            console.error('Sync error:', e);
                                        }
                                        Swal.fire({
                                            title: 'Pembayaran Berhasil!',
                                            text: 'Tiket QRIS walk-in berhasil divalidasi.',
                                            icon: 'success',
                                            confirmButtonColor: '#1E5128',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    },
                                    onPending: async function(result) {
                                        try {
                                            await fetch(`/staff/ticketing/sync/${data.reservation_id}`, {
                                                method: 'POST',
                                                headers: {
                                                    'X-Requested-With': 'XMLHttpRequest',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                }
                                            });
                                        } catch (e) {
                                            console.error('Sync error:', e);
                                        }
                                        Swal.fire({
                                            title: 'Menunggu Pembayaran',
                                            text: 'Silakan selesaikan pembayaran QRIS pada aplikasi Anda.',
                                            icon: 'info',
                                            confirmButtonColor: '#1E5128',
                                            confirmButtonText: 'OK'
                                        }).then(() => {
                                            window.location.reload();
                                        });
                                    },
                                    onError: function(result) {
                                        Swal.fire({
                                            title: 'Gagal',
                                            text: 'Pembayaran QRIS gagal diproses.',
                                            icon: 'error',
                                            confirmButtonColor: '#1E5128'
                                        });
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = originalText;
                                    },
                                    onClose: function() {
                                        Swal.fire({
                                            title: 'Info',
                                            text: 'Pop-up pembayaran QRIS ditutup.',
                                            icon: 'info',
                                            confirmButtonColor: '#1E5128'
                                        });
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = originalText;
                                    }
                                });
                            }
                        } else {
                            Swal.fire({
                                title: 'Gagal',
                                text: data.message || 'Terjadi kesalahan sistem.',
                                icon: 'error',
                                confirmButtonColor: '#1E5128'
                            });
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire({
                            title: 'Terjadi Kesalahan',
                            text: 'Gagal memproses pembayaran. Coba lagi.',
                            icon: 'error',
                            confirmButtonColor: '#1E5128'
                        });
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            }
        });
    </script>
@endpush
