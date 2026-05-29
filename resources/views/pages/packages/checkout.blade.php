@extends('layouts.app')
@section('title', 'Checkout Paket - Penglipuran')
@section('header_title', 'Checkout Paket')

@push('styles')
    @if(config('midtrans.is_production'))
        <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @else
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif
@endpush

@section('content')
    <div class="relative pb-32" x-data="checkoutForm()">
        <div class="px-5 py-6">
            <div class="mb-4">
                <span class="text-primary mb-2 inline-block rounded-lg border border-green-100 bg-green-50 px-2.5 py-1 text-xs font-bold">Checkout</span>
                <h1 class="text-charcoal text-xl font-bold">{{ $package->name }}</h1>
                <p class="text-sm text-gray-500 mt-1">Rp {{ number_format($package->price, 0, ',', '.') }} / orang</p>
            </div>

            <!-- Form -->
            <form id="checkout-form" @submit.prevent="processPayment" class="space-y-6">
                @csrf
                
                <!-- Party Size Stepper -->
                <div>
                    <h3 class="text-charcoal mb-3 font-bold">Jumlah Peserta (Party Size)</h3>
                    <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <div>
                            <div class="text-charcoal font-bold">Peserta</div>
                            <div class="mt-0.5 text-xs text-gray-500">Minimal {{ $package->min_capacity }} Orang</div>
                        </div>

                        <div class="flex items-center gap-4 rounded-full border border-gray-200 bg-white px-2 py-1.5 shadow-sm">
                            <button type="button" @click="if(partySize > {{ $package->min_capacity }}) partySize--"
                                class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-500 active:bg-gray-100">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <input type="hidden" name="party_size" x-model="partySize">
                            <span class="text-charcoal w-4 text-center text-lg font-bold" x-text="partySize"></span>
                            <button type="button" @click="if(partySize < {{ $package->max_capacity }}) partySize++"
                                class="bg-primary/10 text-primary active:bg-primary/20 flex h-8 w-8 items-center justify-center rounded-full">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Schedule / Jadwal Kunjungan (Traveloka-Style Selector) -->
                <div class="space-y-3">
                    <h3 class="text-charcoal font-bold">Jadwal Kunjungan</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Date Trigger Button -->
                        <button type="button" @click="isOpenCalendarModal = true"
                            class="flex flex-col items-start gap-1 rounded-2xl border border-gray-100 bg-gray-50 p-4 text-left transition-all hover:border-green-200 active:scale-[0.98]">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal Kunjungan</span>
                            <div class="flex items-center gap-2 mt-1 w-full overflow-hidden">
                                <svg class="h-5 w-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-charcoal text-sm font-bold truncate" x-text="formatDateLong(selectedDate)"></span>
                            </div>
                            <input type="hidden" name="scheduled_date" :value="selectedDate">
                        </button>

                        <!-- Time Trigger Button -->
                        <button type="button" @click="isOpenTimeModal = true"
                            class="flex flex-col items-start gap-1 rounded-2xl border border-gray-100 bg-gray-50 p-4 text-left transition-all hover:border-green-200 active:scale-[0.98]">
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Waktu Kunjungan</span>
                            <div class="flex items-center gap-2 mt-1 w-full overflow-hidden">
                                <svg class="h-5 w-5 text-primary shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-charcoal text-sm font-bold" x-text="selectedTime + ' WIB'"></span>
                            </div>
                            <input type="hidden" name="scheduled_time" :value="selectedTime">
                        </button>
                    </div>
                </div>

                <!-- Date Calendar Bottom-Sheet / Modal -->
                <div x-show="isOpenCalendarModal" 
                    class="fixed inset-0 z-50 flex items-end justify-center md:items-center p-0 md:p-4"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="display: none;">
                    
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-charcoal/60 backdrop-blur-xs" @click="isOpenCalendarModal = false"></div>

                    <!-- Sheet/Modal Body -->
                    <div class="relative w-full max-w-md rounded-t-3xl md:rounded-3xl bg-white p-6 shadow-2xl transition-all z-10 max-h-[85vh] md:max-h-[90vh] overflow-y-auto flex flex-col"
                        x-show="isOpenCalendarModal"
                        x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="translate-y-full md:translate-y-4 md:scale-95"
                        x-transition:enter-end="translate-y-0 md:translate-y-0 md:scale-100"
                        x-transition:leave="transition ease-in duration-200 transform"
                        x-transition:leave-start="translate-y-0 md:translate-y-0 md:scale-100"
                        x-transition:leave-end="translate-y-full md:translate-y-4 md:scale-95">
                        
                        <!-- Pull Handle for Mobile -->
                        <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-gray-200 md:hidden"></div>

                        <!-- Header -->
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-charcoal text-lg font-bold">Pilih Tanggal Kunjungan</h3>
                            <button type="button" @click="isOpenCalendarModal = false" class="rounded-full p-1 text-gray-400 hover:bg-gray-100">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Calendar Controller -->
                        <div class="mb-4 flex items-center justify-between rounded-xl bg-gray-50 p-2">
                            <button type="button" @click="prevMonth()" :disabled="isPrevMonthDisabled()"
                                class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-white hover:shadow-xs disabled:opacity-30">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <span class="text-charcoal text-sm font-bold uppercase tracking-wider" x-text="currentMonthName + ' ' + currentYear"></span>
                            <button type="button" @click="nextMonth()"
                                class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-white hover:shadow-xs">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>

                        <!-- Days of Week Header -->
                        <div class="mb-2 grid grid-cols-7 text-center text-xs font-bold text-gray-400">
                            <div>MIN</div>
                            <div>SEN</div>
                            <div>SEL</div>
                            <div>RAB</div>
                            <div>KAM</div>
                            <div>JUM</div>
                            <div>SAB</div>
                        </div>

                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-1 text-center text-sm font-medium">
                            <template x-for="day in calendarDays">
                                <div class="aspect-square flex items-center justify-center p-0.5">
                                    <!-- Empty/Previous/Next Month Cells -->
                                    <template x-if="!day.isCurrentMonth">
                                        <span class="text-gray-300 w-full h-full flex items-center justify-center text-xs" x-text="day.dayNum"></span>
                                    </template>
                                    
                                    <!-- Current Month Date Selection -->
                                    <template x-if="day.isCurrentMonth">
                                        <button type="button" 
                                            @click="if(!day.disabled) { selectedDate = day.value; isOpenCalendarModal = false; }"
                                            :disabled="day.disabled"
                                            class="relative w-full h-full rounded-full flex flex-col items-center justify-center text-xs font-bold transition-all"
                                            :class="{
                                                'bg-primary text-white shadow-md shadow-primary/30': selectedDate === day.value,
                                                'text-gray-300 cursor-not-allowed line-through': day.disabled,
                                                'text-charcoal hover:bg-green-50 hover:text-primary': selectedDate !== day.value && !day.disabled
                                            }">
                                            <span x-text="day.dayNum"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                        
                        <div class="mt-6 border-t border-gray-100 pt-4 flex justify-between items-center text-xs text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-primary"></span> Terpilih
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-gray-200"></span> Tidak Tersedia
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Time Selection Bottom-Sheet / Modal -->
                <div x-show="isOpenTimeModal" 
                    class="fixed inset-0 z-50 flex items-end justify-center md:items-center p-0 md:p-4"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="display: none;">
                    
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-charcoal/60 backdrop-blur-xs" @click="isOpenTimeModal = false"></div>

                    <!-- Sheet/Modal Body -->
                    <div class="relative w-full max-w-md rounded-t-3xl md:rounded-3xl bg-white p-6 shadow-2xl transition-all z-10 max-h-[85vh] md:max-h-[90vh] overflow-y-auto flex flex-col"
                        x-show="isOpenTimeModal"
                        x-transition:enter="transition ease-out duration-300 transform"
                        x-transition:enter-start="translate-y-full md:translate-y-4 md:scale-95"
                        x-transition:enter-end="translate-y-0 md:translate-y-0 md:scale-100"
                        x-transition:leave="transition ease-in duration-200 transform"
                        x-transition:leave-start="translate-y-0 md:translate-y-0 md:scale-100"
                        x-transition:leave-end="translate-y-full md:translate-y-4 md:scale-95">
                        
                        <!-- Pull Handle for Mobile -->
                        <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-gray-200 md:hidden"></div>

                        <!-- Header -->
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-charcoal text-lg font-bold">Pilih Waktu Kunjungan</h3>
                            <button type="button" @click="isOpenTimeModal = false" class="rounded-full p-1 text-gray-400 hover:bg-gray-100">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Popular Slots Section -->
                        <div class="mb-6">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Pilihan Slot Populer</h4>
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="slot in timeSlots">
                                    <button type="button"
                                        @click="selectedTime = slot; isOpenTimeModal = false;"
                                        class="h-11 rounded-xl font-bold transition-all border text-sm flex items-center justify-center"
                                        :class="{
                                            'bg-primary text-white border-primary shadow-sm shadow-primary/20': selectedTime === slot,
                                            'bg-gray-50 border-gray-100 text-charcoal hover:bg-green-50 hover:text-primary': selectedTime !== slot
                                        }"
                                        x-text="slot + ' WIB'">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="relative flex py-3 items-center">
                            <div class="grow border-t border-gray-100"></div>
                            <span class="shrink mx-4 text-xs font-semibold text-gray-400 uppercase">Atau Atur Jam Kustom</span>
                            <div class="grow border-t border-gray-100"></div>
                        </div>

                        <!-- Custom Clock Input Section -->
                        <div class="mt-4">
                            <p class="text-xs text-gray-500 mb-3 text-center">Bebas memilih jam operasional berkunjung (07:00 - 18:00 WIB)</p>
                            <div class="flex items-center justify-center gap-3">
                                <!-- Modern Time Input -->
                                <div class="relative flex items-center bg-gray-50 border border-gray-100 rounded-2xl px-4 py-3 w-40 justify-center">
                                    <input type="time" x-model="selectedTime" min="07:00" max="18:00"
                                        class="w-full text-center text-lg font-bold text-charcoal bg-transparent focus:outline-none focus:ring-0 border-0 p-0">
                                </div>
                                
                                <button type="button" @click="isOpenTimeModal = false"
                                    class="bg-primary text-white font-bold px-6 py-3.5 rounded-2xl text-sm transition-all hover:bg-primary-dark shadow-md shadow-primary/30">
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-charcoal mb-3 font-bold">Informasi Kontak Pemesan</h3>
                    <div class="space-y-3">
                        <input type="text" name="guest_name" value="{{ auth()->user()->name }}" required placeholder="Nama Lengkap"
                            class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
                        <input type="email" name="guest_email" value="{{ auth()->user()->email }}" required placeholder="Alamat Email (Untuk E-Ticket)"
                            class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
                        <input type="tel" name="guest_phone" value="{{ auth()->user()->phone ?? '' }}" required placeholder="Nomor WhatsApp aktif"
                            class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
                    </div>
                </div>
                
                <!-- Error Message -->
                <div x-show="errorMessage" class="bg-red-50 text-red-600 p-3 rounded-xl text-sm" x-text="errorMessage" style="display: none;"></div>

                <div class="fixed inset-x-0 bottom-0 z-30 border-t border-gray-100 bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] shadow-[0_-4px_10px_rgba(0,0,0,0.05)]">
                    <div class="mb-3 flex items-center justify-between px-1">
                        <span class="text-sm font-medium text-gray-500">Total Harga</span>
                        <span class="text-primary text-lg font-bold">Rp <span x-text="formattedTotal"></span></span>
                    </div>
                    <button type="submit" :disabled="isLoading"
                        class="bg-primary shadow-primary/30 flex h-14 w-full items-center justify-center gap-2 rounded-2xl font-bold text-white shadow-lg transition-all active:scale-[0.98] disabled:opacity-50">
                        <span x-show="!isLoading">Bayar Sekarang</span>
                        <span x-show="isLoading">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
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
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return months[this.currentMonth];
            },
            
            isPrevMonthDisabled() {
                const today = new Date();
                return this.currentYear <= today.getFullYear() && this.currentMonth <= today.getMonth();
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
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
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
                    const response = await fetch("{{ route('tour-package.process', $package->id) }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: function(result) {
                                window.location.href = "{{ route('bookings') }}?status=success";
                            },
                            onPending: function(result) {
                                window.location.href = "{{ route('bookings') }}?status=pending";
                            },
                            onError: function(result) {
                                this.errorMessage = 'Pembayaran gagal atau dibatalkan.';
                                this.isLoading = false;
                            }.bind(this),
                            onClose: function() {
                                this.errorMessage = 'Anda menutup popup pembayaran sebelum menyelesaikannya.';
                                this.isLoading = false;
                            }.bind(this)
                        });
                    } else {
                        this.errorMessage = data.message || 'Terjadi kesalahan pada sistem.';
                        this.isLoading = false;
                    }
                } catch (error) {
                    this.errorMessage = 'Terjadi kesalahan koneksi. Silakan coba lagi.';
                    this.isLoading = false;
                }
            }
        }));
    });
</script>
@endpush