@extends('layouts.app')
@section('title', __('Checkout Paket - Penglipuran'))
@section('header_title', __('Checkout Paket'))

@push('styles')
    @if (config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif
@endpush

@section('content')
    <div class="relative pb-32 lg:pb-8" x-data="checkoutForm()">
        <div class="mx-auto w-full max-w-5xl px-5 py-6 md:px-6 lg:px-8 lg:py-8">
            <div class="mb-4 lg:mb-6">
                <span
                    class="text-primary mb-2 inline-block rounded-lg border border-green-100 bg-green-50 px-2.5 py-1 text-xs font-bold">{{ __('Checkout') }}</span>
                <h1 class="text-charcoal text-xl font-bold lg:text-2xl">{{ $package->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('Rp :price / orang', ['price' => number_format($package->price, 0, ',', '.')]) }}</p>
            </div>

            <!-- Form -->
            <form id="checkout-form" @submit.prevent="processPayment"
                class="lg:grid lg:grid-cols-[1fr_22rem] lg:items-start lg:gap-8">
                @csrf

                <div class="space-y-6">
                    @include('user.packages.partials.party-size')

                    @include('user.packages.partials.schedule-selector')

                    @include('user.packages.partials.contact-info')

                    <!-- Error Message -->
                    <div x-show="errorMessage" class="rounded-xl bg-red-50 p-3 text-sm text-red-600" x-text="errorMessage"
                        style="display: none;"></div>
                </div>

                @include('user.packages.partials.payment-bar')
            </form>
        </div>

        @include('user.packages.partials.calendar-modal')
        @include('user.packages.partials.time-modal')
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutForm', () => ({
                partySize: {{ $package->min_capacity }},
                pricePerPax: {{ $package->price }},
                isLoading: false,
                errorMessage: '',

                // Traveloka-style Dynamic Date & Time Picker
                selectedDate: '{{ date('Y-m-d') }}',
                selectedTime: '09:00',
                isOpenCalendarModal: false,
                isOpenTimeModal: false,

                currentYear: new Date().getFullYear(),
                currentMonth: new Date().getMonth(), // 0-indexed

                get calendarDays() {
                    const year = this.currentYear;
                    const month = this.currentMonth;
                    const firstDayIndex = new Date(year, month, 1).getDay();
                    const totalDays = new Date(year, month + 1, 0).getDate();
                    const prevMonthTotalDays = new Date(year, month, 0).getDate();
                    const days = [];

                    // Prefix days from previous month
                    for (let i = firstDayIndex - 1; i >= 0; i--) {
                        days.push({
                            dayNum: prevMonthTotalDays - i,
                            isCurrentMonth: false,
                            value: '',
                            disabled: true
                        });
                    }

                    // Current month days
                    const todayStr = new Date().toISOString().split('T')[0];
                    for (let d = 1; d <= totalDays; d++) {
                        const dateObj = new Date(year, month, d);
                        const yyyy = dateObj.getFullYear();
                        const mm = String(dateObj.getMonth() + 1).padStart(2, '0');
                        const dd = String(dateObj.getDate()).padStart(2, '0');
                        const dateVal = `${yyyy}-${mm}-${dd}`;

                        const isPast = dateVal < todayStr;

                        days.push({
                            dayNum: d,
                            isCurrentMonth: true,
                            value: dateVal,
                            disabled: isPast
                        });
                    }

                    // Suffix cells
                    const remainingCells = 42 - days.length;
                    for (let i = 1; i <= remainingCells; i++) {
                        days.push({
                            dayNum: i,
                            isCurrentMonth: false,
                            value: '',
                            disabled: true
                        });
                    }

                    return days;
                },

                get currentMonthName() {
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                        'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    return months[this.currentMonth];
                },

                isPrevMonthDisabled() {
                    const today = new Date();
                    return this.currentYear <= today.getFullYear() && this.currentMonth <= today
                        .getMonth();
                },

                prevMonth() {
                    if (this.isPrevMonthDisabled()) return;
                    if (this.currentMonth === 0) {
                        this.currentMonth = 11;
                        this.currentYear--;
                    } else {
                        this.currentMonth--;
                    }
                },

                nextMonth() {
                    if (this.currentMonth === 11) {
                        this.currentMonth = 0;
                        this.currentYear++;
                    } else {
                        this.currentMonth++;
                    }
                },

                formatDateLong(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
                        'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    return `${days[d.getDay()]}, ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
                },

                timeSlots: ['09:00', '11:00', '13:00', '15:00', '17:00'],

                get totalAmount() {
                    return this.partySize * this.pricePerPax;
                },

                get formattedTotal() {
                    return new Intl.NumberFormat('id-ID').format(this.totalAmount);
                },

                async processPayment(e) {
                    this.isLoading = true;
                    this.errorMessage = '';

                    const form = e.target;
                    const formData = new FormData(form);

                    try {
                        const response = await fetch(
                            "{{ route('tour-package.process', $package->id) }}", {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: formData
                            });

                        const data = await response.json();

                        if (data.success && data.snap_token) {
                            const isProduction = {{ config('midtrans.is_production') ? 'true' : 'false' }};
                            if (!isProduction) {
                                window.location.href = `https://app.sandbox.midtrans.com/snap/v2/vtweb/${data.snap_token}`;
                                return;
                            }

                            snap.pay(data.snap_token, {
                                onSuccess: function(result) {
                                    window.location.href =
                                        "{{ route('bookings') }}?status=success";
                                },
                                onPending: function(result) {
                                    window.location.href =
                                        "{{ route('bookings') }}?status=pending";
                                },
                                onError: function(result) {
                                    this.errorMessage =
                                        '{{ __('Pembayaran gagal atau dibatalkan.') }}';
                                    this.isLoading = false;
                                }.bind(this),
                                onClose: function() {
                                    this.errorMessage =
                                        '{{ __('Anda menutup popup pembayaran sebelum menyelesaikannya.') }}';
                                    this.isLoading = false;
                                }.bind(this)
                            });
                        } else {
                            this.errorMessage = data.message || '{{ __('Terjadi kesalahan pada sistem.') }}';
                            this.isLoading = false;
                        }
                    } catch (error) {
                        this.errorMessage = '{{ __('Terjadi kesalahan koneksi. Silakan coba lagi.') }}';
                        this.isLoading = false;
                    }
                }
            }));
        });
    </script>
@endpush
