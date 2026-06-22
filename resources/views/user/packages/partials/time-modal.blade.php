<!-- Time Selection Bottom-Sheet / Modal -->
<x-modal name="time-modal" maxWidth="md">
    <!-- Header -->
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-charcoal text-lg font-bold">Pilih Waktu Kunjungan</h3>
        <button type="button" @click="isOpen = false"
            class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:text-gray-600 active:scale-95 md:hidden"
            title="Tutup">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Popular Slots Section -->
    <div class="mb-6">
        <h4 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">Pilihan Slot Populer</h4>
        <div class="grid grid-cols-3 gap-2">
            <template x-for="slot in timeSlots">
                <button type="button" @click="selectedTime = slot; isOpen = false;"
                    class="flex h-11 items-center justify-center rounded-xl border text-sm font-bold transition-all"
                    :class="{
                        'bg-primary text-white border-primary shadow-sm shadow-primary/20': selectedTime === slot,
                        'bg-gray-50 border-gray-100 text-charcoal hover:bg-green-50 hover:text-primary': selectedTime !==
                            slot
                    }"
                    x-text="slot + ' WITA'">
                </button>
            </template>
        </div>
    </div>

    <!-- Divider -->
    <div class="relative flex items-center py-3">
        <div class="grow border-t border-gray-100"></div>
        <span class="mx-4 shrink text-xs font-semibold uppercase text-gray-400">Atau Atur Jam Kustom</span>
        <div class="grow border-t border-gray-100"></div>
    </div>

    <!-- Custom Clock Input Section -->
    <div class="mt-4">
        {{-- TODO: Jam operasional --}}
        <p class="mb-3 text-center text-xs text-gray-500">Bebas memilih jam operasional berkunjung (07:00 - 18:00 WITA)
        </p>
        <div class="flex items-center justify-center gap-3">
            <!-- Modern Time Input -->
            <div
                class="relative flex w-40 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3">
                <input type="time" x-model="selectedTime" min="07:00" max="18:00"
                    class="text-charcoal w-full border-0 bg-transparent p-0 text-center text-lg font-bold focus:outline-none focus:ring-0">
            </div>

            <button type="button" @click="isOpen = false"
                class="bg-primary hover:bg-primary-dark shadow-primary/30 rounded-2xl px-6 py-3.5 text-sm font-bold text-white shadow-md transition-all">
                Simpan
            </button>
        </div>
    </div>
</x-modal>
