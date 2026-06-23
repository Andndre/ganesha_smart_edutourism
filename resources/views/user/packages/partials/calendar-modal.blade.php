<!-- Date Calendar Bottom-Sheet / Modal -->
<x-modal name="calendar-modal" maxWidth="md">
    <!-- Header -->
    <div class="mb-4 flex items-center justify-between">
        <h3 class="text-charcoal text-lg font-bold">{{ __('Pilih Tanggal Kunjungan') }}</h3>
        <button type="button" @click="isOpen = false"
            class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition-all hover:text-gray-600 active:scale-95 md:hidden"
            title="{{ __('Tutup') }}">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Calendar Controller -->
    <div class="mb-4 flex items-center justify-between rounded-xl bg-gray-50 p-2">
        <button type="button" @click="prevMonth()" :disabled="isPrevMonthDisabled()"
            class="hover:shadow-xs flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-white disabled:opacity-30">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <span class="text-charcoal text-sm font-bold uppercase tracking-wider"
            x-text="currentMonthName + ' ' + currentYear"></span>
        <button type="button" @click="nextMonth()"
            class="hover:shadow-xs flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <!-- Days of Week Header -->
    <div class="mb-2 grid grid-cols-7 text-center text-xs font-bold text-gray-400">
        <div>{{ __('MIN') }}</div>
        <div>{{ __('SEN') }}</div>
        <div>{{ __('SEL') }}</div>
        <div>{{ __('RAB') }}</div>
        <div>{{ __('KAM') }}</div>
        <div>{{ __('JUM') }}</div>
        <div>{{ __('SAB') }}</div>
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7 gap-1 text-center text-sm font-medium">
        <template x-for="day in calendarDays">
            <div class="flex aspect-square items-center justify-center p-0.5">
                <!-- Empty/Previous/Next Month Cells -->
                <template x-if="!day.isCurrentMonth">
                    <span class="flex h-full w-full items-center justify-center text-xs text-gray-300"
                        x-text="day.dayNum"></span>
                </template>

                <!-- Current Month Date Selection -->
                <template x-if="day.isCurrentMonth">
                    <button type="button" @click="if(!day.disabled) { selectedDate = day.value; isOpen = false; }"
                        :disabled="day.disabled"
                        class="relative flex h-full w-full flex-col items-center justify-center rounded-full text-xs font-bold transition-all"
                        :class="{
                            'bg-primary text-white shadow-md shadow-primary/30': selectedDate === day.value,
                            'text-gray-300 cursor-not-allowed line-through': day.disabled,
                            'text-charcoal hover:bg-green-50 hover:text-primary': selectedDate !== day.value && !day
                                .disabled
                        }">
                        <span x-text="day.dayNum"></span>
                    </button>
                </template>
            </div>
        </template>
    </div>

    <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4 text-xs text-gray-500">
        <span class="flex items-center gap-1.5">
            <span class="bg-primary h-2 w-2 rounded-full"></span> {{ __('Terpilih') }}
        </span>
        <span class="flex items-center gap-1.5">
            <span class="h-2 w-2 rounded-full bg-gray-200"></span> {{ __('Tidak Tersedia') }}
        </span>
    </div>
</x-modal>
