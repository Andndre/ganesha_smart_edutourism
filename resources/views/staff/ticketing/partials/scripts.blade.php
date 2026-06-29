@if (config('midtrans.is_production'))
    <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
@endif

@php
    $ticketingMessages = [
        'checkin_title' => 'Konfirmasi Masuk',
        'checkin_text' => 'Apakah Anda yakin ingin melakukan check-in untuk pengunjung ini?',
        'checkin_confirm' => 'Ya, Check-in!',
        'cancel' => 'Batal',
        'success_title' => 'Berhasil!',
        'failed_title' => 'Gagal',
        'error_occurred' => 'Terjadi kesalahan.',
        'error_title' => 'Terjadi Kesalahan',
        'checkin_fail' => 'Gagal memproses check-in.',
        'paid_label' => 'Sudah Dibayar',
        'unpaid_label' => 'Belum Dibayar',
        'sync_complete' => 'Sinkronisasi Selesai',
        'status_ticket_prefix' => 'Status tiket saat ini:',
        'sync_fail' => 'Gagal sinkronisasi data dari Midtrans.',
        'cancel_title' => 'Batalkan Tiket',
        'cancel_text' => 'Apakah Anda yakin ingin membatalkan pesanan tiket ini?',
        'cancel_confirm' => 'Ya, Batalkan!',
        'cancel_back' => 'Kembali',
        'cancelled_title' => 'Dibatalkan!',
        'cancel_fail' => 'Gagal membatalkan tiket.',
        'waiting_payment_title' => 'Menunggu Pembayaran',
        'qris_new_tab_text' => 'Silakan selesaikan pembayaran QRIS pada tab baru yang terbuka. Setelah selesai, klik OK.',
        'ok' => 'OK',
        'payment_success_title' => 'Pembayaran Berhasil!',
        'qris_validated' => 'Tiket QRIS walk-in berhasil divalidasi.',
        'qris_app_text' => 'Silakan selesaikan pembayaran QRIS pada aplikasi Anda.',
        'payment_failed_title' => 'Gagal',
        'qris_fail_text' => 'Pembayaran QRIS gagal diproses.',
        'info_title' => 'Info',
        'popup_closed' => 'Pop-up pembayaran QRIS ditutup.',
        'token_fail' => 'Gagal mendapatkan token pembayaran.',
        'processing' => 'Memproses...',
        'system_error' => 'Terjadi kesalahan sistem.',
        'payment_fail_retry' => 'Gagal memproses pembayaran. Coba lagi.',
        'payment_qris_fail' => 'Gagal memproses pembayaran QRIS.',
    ];
@endphp

<script>
    const _t = @json($ticketingMessages);

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
            title: _t.checkin_title,
            text: _t.checkin_text,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1E5128',
            cancelButtonColor: '#d33',
            confirmButtonText: _t.checkin_confirm,
            cancelButtonText: _t.cancel
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
                Swal.fire(_t.success_title, data.message, 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire(_t.failed_title, data.message || _t.error_occurred, 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire(_t.error_title, _t.checkin_fail, 'error');
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
                const statusText = data.status === 'completed' ? _t.paid_label : _t.unpaid_label;
                const icon = data.status === 'completed' ? 'success' : 'info';
                Swal.fire({
                    title: _t.sync_complete,
                    text: _t.status_ticket_prefix + ' ' + statusText,
                    icon: icon,
                    confirmButtonColor: '#1E5128'
                }).then(() => {
                    window.location.reload();
                });
            }
        } catch (error) {
            Swal.close();
            console.error(error);
            Swal.fire(_t.failed_title, _t.sync_fail, 'error');
        }
    }

    async function cancelReservation(id) {
        const {
            isConfirmed
        } = await Swal.fire({
            title: _t.cancel_title,
            text: _t.cancel_text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: _t.cancel_confirm,
            cancelButtonText: _t.cancel_back
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
                Swal.fire(_t.cancelled_title, data.message, 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire(_t.failed_title, data.message || _t.error_occurred, 'error');
            }
        } catch (error) {
            console.error(error);
            Swal.fire(_t.error_title, _t.cancel_fail, 'error');
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
                        title: _t.waiting_payment_title,
                        text: _t.qris_new_tab_text,
                        icon: 'info',
                        confirmButtonColor: '#1E5128',
                        confirmButtonText: _t.ok
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
                            title: _t.payment_success_title,
                            text: _t.qris_validated,
                            icon: 'success',
                            confirmButtonColor: '#1E5128',
                            confirmButtonText: _t.ok
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
                            title: _t.waiting_payment_title,
                            text: _t.qris_app_text,
                            icon: 'info',
                            confirmButtonColor: '#1E5128',
                            confirmButtonText: _t.ok
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    onError: function(result) {
                        Swal.fire({
                            title: _t.payment_failed_title,
                            text: _t.qris_fail_text,
                            icon: 'error',
                            confirmButtonColor: '#1E5128'
                        });
                    },
                    onClose: function() {
                        Swal.fire({
                            title: _t.info_title,
                            text: _t.popup_closed,
                            icon: 'info',
                            confirmButtonColor: '#1E5128'
                        });
                    }
                });
            } else {
                Swal.fire(_t.failed_title, data.message || _t.token_fail, 'error');
            }
        } catch (error) {
            Swal.close();
            console.error(error);
            Swal.fire(_t.error_title, _t.payment_qris_fail, 'error');
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
                    <span class="ml-2">{{ 'Memproses...' }}</span>
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
                                title: _t.success_title,
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#1E5128',
                                confirmButtonText: _t.ok
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
                                    title: _t.waiting_payment_title,
                                    text: _t.qris_new_tab_text,
                                    icon: 'info',
                                    confirmButtonColor: '#1E5128',
                                    confirmButtonText: _t.ok
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
                                        title: _t.payment_success_title,
                                        text: _t.qris_validated,
                                        icon: 'success',
                                        confirmButtonColor: '#1E5128',
                                        confirmButtonText: _t.ok
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
                                        title: _t.waiting_payment_title,
                                        text: _t.qris_app_text,
                                        icon: 'info',
                                        confirmButtonColor: '#1E5128',
                                        confirmButtonText: _t.ok
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                },
                                onError: function(result) {
                                    Swal.fire({
                                        title: _t.payment_failed_title,
                                        text: _t.qris_fail_text,
                                        icon: 'error',
                                        confirmButtonColor: '#1E5128'
                                    });
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalText;
                                },
                                onClose: function() {
                                    Swal.fire({
                                        title: _t.info_title,
                                        text: _t.popup_closed,
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
                            title: _t.failed_title,
                            text: data.message || _t.system_error,
                            icon: 'error',
                            confirmButtonColor: '#1E5128'
                        });
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire({
                        title: _t.error_title,
                        text: _t.payment_fail_retry,
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
