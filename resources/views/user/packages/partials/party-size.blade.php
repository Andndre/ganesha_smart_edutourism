<!-- Party Size Stepper -->
<div>
    <h3 class="text-charcoal mb-3 font-bold">{{ __('Jumlah Peserta (Party Size)') }}</h3>
    <div class="flex items-center justify-between rounded-2xl border border-gray-100 bg-gray-50 p-4">
        <div>
            <div class="text-charcoal font-bold">{{ __('Peserta') }}</div>
            <div class="mt-0.5 text-xs text-gray-500">{{ __('Minimal :count Orang', ['count' => $package->min_capacity]) }}</div>
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
