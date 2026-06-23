<!-- Schedule / Jadwal Kunjungan (Traveloka-Style Selector) -->
<div class="space-y-3">
    <h3 class="text-charcoal font-bold">{{ __('Jadwal Kunjungan') }}</h3>
    <div class="grid grid-cols-2 gap-3">
        <!-- Date Trigger Button -->
        <button type="button" @click="$dispatch('open-calendar-modal')"
            class="flex flex-col items-center justify-center rounded-2xl border p-3 transition-all active:scale-[0.98]"
            :class="selectedDate ? 'border-primary bg-green-50 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300'">
            <div class="text-primary mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <span class="mb-0.5 text-xs font-bold text-gray-500">{{ __('Tanggal Kunjungan') }}</span>
            <span class="text-charcoal text-sm font-black"
                x-text="selectedDate ? formatDateLong(selectedDate) : '{{ __('Pilih Tanggal') }}'"></span>
            <input type="hidden" name="scheduled_date" :value="selectedDate">
        </button>

        <!-- Time Trigger Button -->
        <button type="button" @click="$dispatch('open-time-modal')"
            class="flex flex-col items-center justify-center rounded-2xl border p-3 transition-all active:scale-[0.98]"
            :class="selectedTime ? 'border-primary bg-green-50 shadow-sm' : 'border-gray-200 bg-white hover:border-gray-300'">
            <div class="text-primary mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="mb-0.5 text-xs font-bold text-gray-500">{{ __('Waktu Kunjungan') }}</span>
            <span class="text-charcoal text-sm font-black"
                x-text="selectedTime ? selectedTime + ' WITA' : '{{ __('Pilih Waktu') }}'"></span>
            <input type="hidden" name="scheduled_time" :value="selectedTime">
        </button>
    </div>
</div>
