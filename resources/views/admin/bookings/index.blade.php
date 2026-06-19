@extends('layouts.dashboard')

@section('title', 'Pemesanan')

@section('content')

    <div class="mb-6">
        <h1 class="font-display text-charcoal text-2xl font-bold">Kelola Pemesanan</h1>
        <p class="mt-0.5 text-sm text-gray-500">Semua transaksi tiket masuk dan paket wisata.</p>
    </div>

    {{-- Filter Tabs --}}
    <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
        @php
            $tabs = [
                'Semua' => 'Semua',
                'Aktif' => 'Aktif',
                'Selesai' => 'Selesai',
                'Dibatalkan' => 'Dibatalkan',
            ];
        @endphp
        @foreach ($tabs as $key => $label)
            <a href="{{ route('admin.bookings', ['status' => $key]) }}"
                class="{{ $statusFilter === $key ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-50' }} shrink-0 rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">ID
                            Booking</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Wisatawan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Paket/Tiket</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Tgl.
                            Kunjungan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Total
                        </th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">
                            Status</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @php
                        $statusLabels = [
                            'pending' => 'Pending',
                            'confirmed' => 'Aktif',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                        ];
                        $statusBadges = [
                            'pending' => 'bg-amber-100 text-amber-800',
                            'confirmed' => 'bg-primary/10 text-primary',
                            'completed' => 'bg-gray-100 text-gray-500',
                            'cancelled' => 'bg-rose-100 text-rose-800',
                        ];
                    @endphp
                    @forelse ($bookings as $b)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-5 py-4 font-mono text-xs text-gray-400">
                                #BK-{{ str_pad($b->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="text-charcoal font-medium">{{ $b->guest_name ?? ($b->user ? $b->user->name : 'N/A') }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-500">
                                {{ $b->tourPackage ? $b->tourPackage->name : 'Tiket Masuk Mandiri' }}</td>
                            <td class="px-5 py-4 text-gray-500">
                                {{ $b->scheduled_date ? $b->scheduled_date->format('d M Y') : '-' }}</td>
                            <td class="text-charcoal px-5 py-4 font-semibold">Rp
                                {{ number_format($b->total_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="{{ $statusBadges[$b->status] ?? 'bg-gray-100 text-gray-500' }} rounded-full px-2.5 py-0.5 text-xs font-semibold">
                                    {{ $statusLabels[$b->status] ?? $b->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <button
                                    onclick="openBookingModal({{ json_encode([
                                        'id' => $b->id,
                                        'guest_name' => $b->guest_name,
                                        'user_name' => $b->user?->name,
                                        'guest_email' => $b->guest_email,
                                        'user_email' => $b->user?->email,
                                        'guest_phone' => $b->guest_phone,
                                        'package_name' => $b->tourPackage ? $b->tourPackage->name : 'Tiket Masuk Mandiri',
                                        'party_size' => $b->party_size,
                                        'scheduled_date' => $b->scheduled_date ? $b->scheduled_date->format('d M Y') : '-',
                                        'scheduled_time' => $b->scheduled_time ? \Carbon\Carbon::parse($b->scheduled_time)->format('H:i') : '',
                                        'total_amount_formatted' => 'Rp ' . number_format($b->total_amount, 0, ',', '.'),
                                        'payment_method' => $b->payment_method,
                                        'payment_reference' => $b->payment_reference,
                                        'status' => $b->status,
                                        'payment_status' => $b->payment_status,
                                    ]) }})"
                                    class="text-primary border-primary/20 hover:bg-primary/5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-gray-400">Tidak ada data pemesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bookings instanceof \Illuminate\Pagination\LengthAwarePaginator && $bookings->hasPages())
            <div class="border-t border-gray-100 px-5 py-3.5">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>

    {{-- Detail & Update Status Modal --}}
    <x-modal name="booking-modal" maxWidth="lg" desktopLayout="drawer">
        <div class="mb-4">
            <h3 class="font-display text-charcoal text-lg font-bold">Detail Pemesanan <span id="modal-booking-id"
                    class="font-mono text-gray-400"></span></h3>
        </div>
        <form id="modal-booking-form" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Wisatawan</p>
                        <p id="modal-guest-name" class="text-charcoal mt-0.5 font-semibold"></p>
                        <p id="modal-guest-email" class="text-xs text-gray-500"></p>
                        <p id="modal-guest-phone" class="text-xs text-gray-500"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Paket Wisata</p>
                        <p id="modal-package-name" class="text-charcoal mt-0.5 font-semibold"></p>
                        <p id="modal-party-size" class="text-xs text-gray-500"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Tgl. Kunjungan</p>
                        <p id="modal-schedule-date" class="text-charcoal mt-0.5 font-semibold"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Pembayaran</p>
                        <p id="modal-total-amount" class="text-primary mt-0.5 font-bold"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Metode & Ref</p>
                        <p id="modal-payment-method" class="text-charcoal mt-0.5 font-semibold"></p>
                        <p id="modal-payment-ref" class="font-mono text-xs text-gray-500"></p>
                    </div>
                </div>

                <hr class="border-gray-100">

                <div class="space-y-3">
                    <div>
                        <label for="modal-status-select"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Status
                            Pemesanan</label>
                        <select name="status" id="modal-status-select"
                            class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Aktif (Dikonfirmasi)</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>
                    <div>
                        <label for="modal-payment-select"
                            class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Status
                            Pembayaran</label>
                        <select name="payment_status" id="modal-payment-select"
                            class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                            <option value="pending">Belum Bayar (Pending)</option>
                            <option value="paid">Lunas (Paid)</option>
                            <option value="refunded">Dikembalikan (Refunded)</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4">
                <button type="button" onclick="closeBookingModal()"
                    class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit"
                    class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </x-modal>

@endsection

@push('scripts')
    <script>
        function openBookingModal(data) {
            document.getElementById('modal-booking-id').innerText = '#BK-' + String(data.id).padStart(4, '0');
            document.getElementById('modal-guest-name').innerText = data.guest_name || data.user_name || 'N/A';
            document.getElementById('modal-guest-email').innerText = data.guest_email || data.user_email || '-';
            document.getElementById('modal-guest-phone').innerText = data.guest_phone || '-';
            document.getElementById('modal-package-name').innerText = data.package_name;
            document.getElementById('modal-party-size').innerText = data.party_size + ' Orang';
            document.getElementById('modal-schedule-date').innerText = data.scheduled_date + (data.scheduled_time ? ' @ ' +
                data.scheduled_time : '');
            document.getElementById('modal-total-amount').innerText = data.total_amount_formatted;
            document.getElementById('modal-payment-method').innerText = data.payment_method || '-';
            document.getElementById('modal-payment-ref').innerText = data.payment_reference || '-';

            document.getElementById('modal-status-select').value = data.status;
            document.getElementById('modal-payment-select').value = data.payment_status || 'pending';

            const form = document.getElementById('modal-booking-form');
            form.action = `/admin/bookings/${data.id}/status`;

            window.dispatchEvent(new CustomEvent('open-booking-modal'));
        }

        function closeBookingModal() {
            window.dispatchEvent(new CustomEvent('close-booking-modal'));
        }
    </script>
@endpush
