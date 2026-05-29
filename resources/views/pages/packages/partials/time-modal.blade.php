<!-- Time Selection Bottom-Sheet / Modal (Moved Outside to prevent inherited space-y margins) -->
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
    <div class="relative w-full max-w-md rounded-t-3xl rounded-b-none md:rounded-3xl bg-white p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))] md:pb-6 shadow-2xl transition-all z-10 max-h-[85vh] md:max-h-[90vh] overflow-y-auto flex flex-col"
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
