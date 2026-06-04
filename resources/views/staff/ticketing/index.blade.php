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
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
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
            <div class="overflow-x-auto rounded-xl border border-gray-100">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Pembeli</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Paket</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Jumlah & Total</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-400">Waktu</th>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
