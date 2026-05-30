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
