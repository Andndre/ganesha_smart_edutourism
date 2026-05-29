<!-- Date Calendar Bottom-Sheet / Modal (Moved Outside to prevent inherited space-y margins) -->
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
    <div class="relative w-full max-w-md rounded-t-3xl rounded-b-none md:rounded-3xl bg-white p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))] md:pb-6 shadow-2xl transition-all z-10 max-h-[85vh] md:max-h-[90vh] overflow-y-auto flex flex-col"
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
