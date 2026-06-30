<!-- Map Style Switcher Modal -->
<x-modal name="map-style-modal" maxWidth="sm">
    <div class="p-4">
        <h3 class="text-lg font-bold text-charcoal mb-4">{{ __('Pilih Jenis Peta') }}</h3>
        <div class="grid grid-cols-2 gap-3">
            <button type="button" id="map-style-option-standard"
                class="map-style-option rounded-2xl border-2 border-primary overflow-hidden">
                <img id="map-style-thumb-standard" class="w-full h-24 object-cover" alt="Peta Standar">
                <div class="py-2 text-sm font-semibold text-charcoal text-center">{{ __('Standar') }}</div>
            </button>
            <button type="button" id="map-style-option-satellite"
                class="map-style-option rounded-2xl border-2 border-transparent overflow-hidden">
                <img id="map-style-thumb-satellite" class="w-full h-24 object-cover" alt="Satelit">
                <div class="py-2 text-sm font-semibold text-charcoal text-center">{{ __('Satelit') }}</div>
            </button>
        </div>
        <p class="text-xs text-gray-400 text-center mt-4">{{ __('Pilih tampilan peta yang sesuai dengan kebutuhan Anda.') }}</p>
    </div>
</x-modal>
