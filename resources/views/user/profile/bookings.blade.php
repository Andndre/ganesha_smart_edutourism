@extends('layouts.app')
@section('title', 'Tiket & Pesanan Saya')
@section('header_title', 'Tiket & Pesanan')

@section('content')
    <div class="px-4 py-6" x-data>
        <!-- Status Filter -->
        <div class="no-scrollbar mb-6 flex gap-2 overflow-x-auto">
            <a href="{{ route('bookings', ['filter' => 'semua']) }}"
                class="{{ (!isset($filter) || $filter === 'semua') ? 'bg-primary font-bold text-white border-primary' : 'border-gray-200 bg-white font-medium text-gray-500 hover:bg-gray-50' }} whitespace-nowrap rounded-full border px-4 py-2 text-sm transition-colors">Semua</a>
            <a href="{{ route('bookings', ['filter' => 'aktif']) }}"
                class="{{ (isset($filter) && $filter === 'aktif') ? 'bg-primary font-bold text-white border-primary' : 'border-gray-200 bg-white font-medium text-gray-500 hover:bg-gray-50' }} whitespace-nowrap rounded-full border px-4 py-2 text-sm transition-colors">Aktif</a>
            <a href="{{ route('bookings', ['filter' => 'selesai']) }}"
                class="{{ (isset($filter) && $filter === 'selesai') ? 'bg-primary font-bold text-white border-primary' : 'border-gray-200 bg-white font-medium text-gray-500 hover:bg-gray-50' }} whitespace-nowrap rounded-full border px-4 py-2 text-sm transition-colors">Selesai</a>
        </div>

        <div class="space-y-4">
            @forelse($reservations as $reservation)
                <div class="relative flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
                    <!-- Status Badge -->
                    @php
                        $statusColor = 'bg-gray-100 text-gray-600';
                        $statusText = 'Menunggu Pembayaran';

                        switch ($reservation->status) {
                            case 'confirmed':
                                $statusColor = 'bg-green-100 text-green-700';
                                $statusText = 'Tiket Aktif';
                                break;
                            case 'cancelled':
                                $statusColor = 'bg-red-100 text-red-700';
                                $statusText = 'Dibatalkan';
                                break;
                            case 'completed':
                                $statusColor = 'bg-blue-100 text-blue-700';
                                $statusText = 'Selesai';
                                break;
                        }
                    @endphp

                    <div
                        class="{{ $statusColor }} absolute right-3 top-3 rounded-lg border border-white/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide">
                        {{ $statusText }}
                    </div>

                    <div class="p-4">
                        <div class="mb-3 flex items-center gap-3">
                            <div
                                class="bg-primary/10 text-primary flex h-12 w-12 shrink-0 items-center justify-center rounded-xl">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                            </div>
                            <div class="pr-20">
                                <p class="mb-0.5 w-32 truncate text-[10px] text-gray-500">Order ID:
                                    {{ $reservation->payment_reference }}</p>
                                <h3 class="text-charcoal line-clamp-2 pr-4 font-bold leading-tight">
                                    {{ $reservation->tourPackage->name ?? 'Paket Wisata' }}</h3>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-y-2 rounded-xl bg-gray-50 p-3 text-sm">
                            <div>
                                <p class="mb-0.5 text-xs text-gray-500">Tanggal</p>
                                <p class="text-charcoal font-medium">
                                    {{ \Carbon\Carbon::parse($reservation->scheduled_date)->translatedFormat('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="mb-0.5 text-xs text-gray-500">Waktu</p>
                                <p class="text-charcoal font-medium">
                                    {{ \Carbon\Carbon::parse($reservation->scheduled_time)->format('H:i') }} WITA</p>
                            </div>
                            <div>
                                <p class="mb-0.5 text-xs text-gray-500">Peserta</p>
                                <p class="text-charcoal font-medium">{{ $reservation->party_size }} Orang</p>
                            </div>
                            <div>
                                <p class="mb-0.5 text-xs text-gray-500">Total Pembayaran</p>
                                <p class="text-primary font-bold">Rp
                                    {{ number_format($reservation->total_amount, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        @if ($reservation->status == 'confirmed')
                            <div class="mt-4 flex gap-2 border-t border-gray-100 pt-4">
                                <button type="button"
                                    @click="$dispatch('open-ticket-modal', { qrUrl: 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ $reservation->qr_code }}', ticketName: '{{ addslashes($reservation->tourPackage->name ?? 'Paket Wisata') }}' })"
                                    class="bg-primary/10 text-primary active:bg-primary/20 flex flex-1 items-center justify-center gap-1.5 rounded-xl py-2.5 text-sm font-bold transition-colors">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Lihat QR Code
                                </button>
                            </div>
                        @elseif($reservation->status == 'pending')
                            <div class="mt-4 flex gap-2 border-t border-gray-100 pt-4">
                                <!-- Could redirect back to midtrans snap page or just show a message -->
                                <button
                                    class="flex-1 rounded-xl bg-yellow-100 py-2.5 text-sm font-bold text-yellow-700 transition-colors active:bg-yellow-200">
                                    Lanjutkan Pembayaran
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="mt-8 rounded-3xl border border-gray-100 bg-white p-8 text-center shadow-sm">
                    <div
                        class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <h3 class="text-charcoal mb-2 text-lg font-bold">Belum Ada Pesanan</h3>
                    <p class="mb-6 text-sm text-gray-500">Anda belum pernah memesan tiket atau paket wisata apapun.</p>
                    <a href="{{ route('tour-packages') }}"
                        class="bg-primary inline-block rounded-xl px-6 py-3 font-bold text-white shadow-md transition-transform active:scale-95">
                        Eksplorasi Paket Wisata
                    </a>
                </div>
            @endforelse
        </div>

    </div>
@endsection

@push('modals')
    <!-- QR Code Modal -->
    <div x-data="{ qrUrl: '', ticketName: '' }" @open-ticket-modal.window="qrUrl = $event.detail.qrUrl; ticketName = $event.detail.ticketName">
        <x-modal name="ticket-modal" maxWidth="sm">
            <div class="text-center">
                <h3 class="text-charcoal mb-1 text-xl font-bold" x-text="ticketName"></h3>
                <p class="mb-6 text-sm text-gray-500">Tunjukkan QR Code ini kepada petugas tiket saat Anda tiba di lokasi wisata.</p>

                <div class="mb-6 flex justify-center rounded-2xl border border-gray-100 bg-gray-50 p-4 shadow-inner">
                    <img :src="qrUrl" alt="QR Code" class="h-48 w-48 rounded-lg">
                </div>

                <button @click="isOpen = false"
                    class="bg-primary block w-full rounded-xl py-3.5 font-bold text-white shadow-lg transition-transform active:scale-[0.98]">
                    Tutup
                </button>
            </div>
        </x-modal>
    </div>
@endpush
