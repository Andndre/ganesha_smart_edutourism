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

                <!-- Schedule -->
                <div>
                    <h3 class="text-charcoal mb-3 font-bold">Jadwal Kunjungan</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="date" name="scheduled_date" required min="{{ date('Y-m-d') }}"
                            class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
                        <input type="time" name="scheduled_time" required
                            class="focus:border-primary focus:ring-primary w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-sm transition-colors focus:outline-none focus:ring-1">
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