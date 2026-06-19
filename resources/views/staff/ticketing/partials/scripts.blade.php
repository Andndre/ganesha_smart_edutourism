@if (config('midtrans.is_production'))
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
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
                        const statusMatch = this.filterStatus === 'all' || res.status === this
                            .filterStatus;
                        const paymentMatch = this.filterPayment === 'all' ||
                            (this.filterPayment === 'cash' ? res.payment_method === 'cash' : res
                                .payment_method !== 'cash');
                        return statusMatch && paymentMatch;
                    })
                    .sort((a, b) => {
                        if (this.sortBy === 'time_desc') return b.timestamp - a.timestamp;
                        if (this.sortBy === 'time_asc') return a.timestamp - b.timestamp;
                        if (this.sortBy === 'amount_desc') return b.total_amount - a
                            .total_amount;
                        if (this.sortBy === 'amount_asc') return a.total_amount - b
                            .total_amount;
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
        const {
            isConfirmed
        } = await Swal.fire({
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
        const {
            isConfirmed
        } = await Swal.fire({
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
                const isProduction = {{ config('midtrans.is_production') ? 'true' : 'false' }};
                if (!isProduction) {
                    const redirectUrl = `https://app.sandbox.midtrans.com/snap/v2/vtweb/${data.snap_token}`;
                    window.open(redirectUrl, '_blank');
                    Swal.fire({
                        title: 'Menunggu Pembayaran',
                        text: 'Silakan selesaikan pembayaran QRIS pada tab baru yang terbuka. Setelah selesai, klik OK.',
                        icon: 'info',
                        confirmButtonColor: '#1E5128',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }

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

                            const isProduction =
                                {{ config('midtrans.is_production') ? 'true' : 'false' }};
                            if (!isProduction) {
                                const redirectUrl =
                                    `https://app.sandbox.midtrans.com/snap/v2/vtweb/${data.snap_token}`;
                                window.open(redirectUrl, '_blank');
                                Swal.fire({
                                    title: 'Menunggu Pembayaran',
                                    text: 'Silakan selesaikan pembayaran QRIS pada tab baru yang terbuka. Setelah selesai, klik OK.',
                                    icon: 'info',
                                    confirmButtonColor: '#1E5128',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
                                });
                                return;
                            }

                            snap.pay(data.snap_token, {
                                onSuccess: async function(result) {
                                    try {
                                        await fetch(
                                            `/staff/ticketing/sync/${data.reservation_id}`, {
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
                                        await fetch(
                                            `/staff/ticketing/sync/${data.reservation_id}`, {
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
