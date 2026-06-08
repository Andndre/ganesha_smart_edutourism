<!-- Time Selection Bottom-Sheet / Modal -->
<x-modal name="time-modal" maxWidth="md">
    <!-- Header -->
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-charcoal text-lg font-bold">Pilih Waktu Kunjungan</h3>
        <button type="button" @click="isOpen = false"
            class="flex items-center justify-center h-8 w-8 rounded-full bg-gray-50 text-gray-400 hover:text-gray-600 active:scale-95 transition-all md:hidden"
            title="Tutup">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Popular Slots Section -->
    <div class="mb-6">
        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Pilihan Slot Populer</h4>
        <div class="grid grid-cols-3 gap-2">
            <template x-for="slot in timeSlots">
                <button type="button"
                    @click="selectedTime = slot; isOpen = false;"
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
            
            <button type="button" @click="isOpen = false"
                class="bg-primary text-white font-bold px-6 py-3.5 rounded-2xl text-sm transition-all hover:bg-primary-dark shadow-md shadow-primary/30">
                Simpan
            </button>
        </div>
    </div>
</x-modal>
