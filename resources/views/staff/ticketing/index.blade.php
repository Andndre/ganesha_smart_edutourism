@extends('layouts.dashboard')

@section('title', 'Layanan Tiket (POS)')

@section('content')

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="font-display text-2xl font-bold text-charcoal">Ticketing Point of Sale</h1>
        <p class="mt-0.5 text-sm text-gray-500">Layanan pembelian tiket walk-in dan verifikasi pengunjung.</p>
    </div>
    <a href="{{ route('staff.ticketing.scan') }}" class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/20 transition-all hover:bg-primary-600 active:scale-[0.98]">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Buka Scanner QR
    </a>
</div>

<div class="max-w-6xl">
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 rounded-xl bg-primary/10 border border-primary/20 p-4 text-sm text-primary">
            <svg class="h-5 w-5 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Form Pembelian -->
        <div class="col-span-1 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 font-display text-lg font-bold text-charcoal">Pembelian Tiket Walk-in</h3>
            <form id="walkin-form" action="{{ route('staff.ticketing.walk-in') }}" method="POST">
                @csrf

                <div class="space-y-4">
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

                    <div class="pt-4">
                        <button type="submit"
                            class="flex w-full justify-center rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary/20 hover:bg-primary-600 active:scale-[0.99] transition-all">
                            Proses & Cetak Tiket
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Transaksi Hari Ini -->
        <div class="col-span-1 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="mb-4 font-display text-lg font-bold text-charcoal">Tiket Terjual Hari Ini</h3>
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
                        @forelse($reservations as $res)
                            <tr class="hover:bg-gray-50/30">
                                <td class="px-4 py-3.5 font-semibold text-charcoal">
                                    {{ $res->user ? $res->user->name : $res->guest_name }}
                                    @if (!$res->user)
                                        <span class="ml-1 inline-flex items-center rounded-lg bg-primary/10 px-2 py-0.5 text-[10px] font-semibold text-primary">Walk-in</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    {{ $res->tourPackage->name ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-600">
                                    {{ $res->party_size }} Org <br>
                                    <span class="text-xs font-semibold text-charcoal">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if ($res->status === 'completed')
                                        <span class="inline-flex rounded-lg bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">Selesai/Masuk</span>
                                    @elseif($res->status === 'confirmed')
                                        <span class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
                                    @else
                                        <span class="inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600">{{ ucfirst($res->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-gray-400 text-xs">
                                    {{ $res->created_at->format('H:i') }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="flex gap-1.5">
                                        @if($res->status === 'confirmed')
                                            <button onclick="checkInReservation({{ $res->id }})" class="inline-flex items-center rounded-lg bg-primary px-2.5 py-1 text-xs font-bold text-white hover:bg-primary-600 transition-all shadow-sm">
                                                Check In
                                            </button>
                                        @elseif($res->status === 'pending')
                                            @if($res->payment_method === 'qris')
                                                <button onclick="payQRIS({{ $res->id }})" class="inline-flex items-center rounded-lg bg-amber-500 px-2.5 py-1 text-xs font-bold text-white hover:bg-amber-600 transition-all shadow-sm">
                                                    Bayar
                                                </button>
                                                <button onclick="syncReservation({{ $res->id }})" class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 text-xs font-bold text-gray-700 hover:bg-gray-200 transition-all" title="Sync Status">
                                                    Sync
                                                </button>
                                            @endif
                                            <button onclick="cancelReservation({{ $res->id }})" class="inline-flex items-center rounded-lg bg-red-50 px-2 py-1 text-xs font-bold text-red-700 hover:bg-red-100 transition-all" title="Batalkan">
                                                Batal
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List (Visible only on Mobile) -->
            <div class="space-y-4 sm:hidden">
                @forelse($reservations as $res)
                    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-bold text-charcoal text-sm">
                                    {{ $res->user ? $res->user->name : $res->guest_name }}
                                </h4>
                                <div class="mt-0.5 flex items-center gap-1.5">
                                    <span class="text-[10px] text-gray-400">Pukul {{ $res->created_at->format('H:i') }}</span>
                                    @if (!$res->user)
                                        <span class="inline-flex items-center rounded bg-primary/10 px-1.5 py-0.2 text-[8px] font-semibold text-primary">Walk-in</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @if ($res->status === 'completed')
                                    <span class="inline-flex rounded-lg bg-primary/10 px-2.5 py-1 text-[10px] font-semibold text-primary">Selesai</span>
                                @elseif($res->status === 'confirmed')
                                    <span class="inline-flex rounded-lg bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700">Menunggu</span>
                                @else
                                    <span class="inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-[10px] font-semibold text-gray-600">{{ ucfirst($res->status) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-xl bg-gray-50/55 p-3 text-xs space-y-1.5">
                            <div class="flex justify-between text-gray-500">
                                <span>Paket Wisata</span>
                                <span class="font-semibold text-charcoal text-right max-w-[150px] truncate">{{ $res->tourPackage->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Jumlah Peserta</span>
                                <span class="font-semibold text-charcoal">{{ $res->party_size }} Orang</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Total Pembayaran</span>
                                <span class="font-bold text-primary">Rp {{ number_format($res->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Actions for Mobile -->
                        @if($res->status === 'confirmed' || $res->status === 'pending')
                            <div class="flex gap-2 pt-1">
                                @if($res->status === 'confirmed')
                                    <button onclick="checkInReservation({{ $res->id }})" class="w-full text-center rounded-xl bg-primary py-2.5 text-xs font-bold text-white shadow-md shadow-primary/15 active:scale-[0.98] transition-all">
                                        Check In / Masuk
                                    </button>
                                @elseif($res->status === 'pending')
                                    @if($res->payment_method === 'qris')
                                        <button onclick="payQRIS({{ $res->id }})" class="flex-1 text-center rounded-xl bg-amber-500 py-2.5 text-xs font-bold text-white shadow-md shadow-amber-500/15 active:scale-[0.98] transition-all">
                                            Bayar
                                        </button>
                                        <button onclick="syncReservation({{ $res->id }})" class="flex-1 text-center rounded-xl bg-gray-100 py-2.5 text-xs font-semibold text-gray-700 active:scale-[0.98] transition-all">
                                            Sync
                                        </button>
                                    @endif
                                    <button onclick="cancelReservation({{ $res->id }})" class="flex-1 text-center rounded-xl bg-red-50 py-2.5 text-xs font-semibold text-red-700 active:scale-[0.98] transition-all">
                                        Batal
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 py-8 text-center text-gray-400 text-sm">Belum ada transaksi hari ini.</div>
                @endforelse
            </div>
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
