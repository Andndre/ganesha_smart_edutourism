{{-- Edit Threshold / Zone Modal --}}
<x-modal name="threshold-modal" maxWidth="2xl" desktopLayout="drawer">
    <div class="mb-4">
        <h3 class="font-display text-charcoal text-lg font-bold" id="modal-title">Edit Zone <span id="modal-zone-name"
                class="text-gray-400"></span></h3>
    </div>
    <form id="modal-threshold-form" method="POST">
        @csrf
        <input type="hidden" name="_method" value="PUT" id="form-method">
        <input type="hidden" name="zone_id" id="field-zone-id" value="">

        <div class="space-y-4">
            <div>
                <label for="modal-name" class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Nama
                    Zona</label>
                <input type="text" name="name" id="modal-name" required
                    class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div id="identifier-group">
                <label for="modal-identifier"
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Identifier
                    (unik)</label>
                <input type="text" name="zone_identifier" id="modal-identifier"
                    class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                @error('zone_identifier')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="modal-max-capacity"
                    class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Kapasitas
                    Maksimal</label>
                <input type="number" name="max_capacity" id="modal-max-capacity" required min="1"
                    class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                @error('max_capacity')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="modal-warning-threshold"
                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Warning (%)</label>
                    <input type="number" name="warning_threshold" id="modal-warning-threshold" required min="1"
                        max="100"
                        class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                    @error('warning_threshold')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="modal-critical-threshold"
                        class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Critical (%)</label>
                    <input type="number" name="critical_threshold" id="modal-critical-threshold" required
                        min="1" max="100"
                        class="focus:border-primary focus:ring-primary text-charcoal w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-sm focus:outline-none focus:ring-1">
                    @error('critical_threshold')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Area Zona
                    (Polygon)</label>
                <p class="mb-2 text-xs text-gray-500">Gunakan alat gambar di map bawah ini untuk menentukan area zona.
                </p>

                <div id="modal-map"
                    class="relative z-0 mb-2 h-75 w-full overflow-hidden rounded-xl border border-gray-200"></div>

                <input type="hidden" name="polygon_coordinates" id="modal-polygon">

                <button type="button" id="btn-clear-polygon"
                    class="mt-2 hidden w-full rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-red-500 hover:bg-gray-50">
                    Hapus & Gambar Ulang
                </button>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4">
            <button type="button" onclick="closeThresholdModal()"
                class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-50">
                Batal
            </button>
            <button type="submit"
                class="bg-primary shadow-primary/20 hover:bg-primary-600 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-md">
                Simpan
            </button>
        </div>
    </form>
</x-modal>

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.dispatchEvent(new CustomEvent('open-threshold-modal'));
        @if(old('_method') == 'PUT')
            document.getElementById('modal-threshold-form').action = "/admin/capacity/{{ old('zone_id') }}/thresholds";
            document.getElementById('form-method').value = "PUT";
            document.getElementById('field-zone-id').value = "{{ old('zone_id') }}";
        @else
            document.getElementById('modal-threshold-form').action = "/admin/capacity";
            document.getElementById('form-method').value = "POST";
            document.getElementById('field-zone-id').value = "";
        @endif
        document.getElementById('modal-name').value = @json(old('name', ''));
        document.getElementById('modal-identifier').value = @json(old('zone_identifier', ''));
        document.getElementById('modal-max-capacity').value = @json(old('max_capacity', ''));
        document.getElementById('modal-warning-threshold').value = @json(old('warning_threshold', ''));
        document.getElementById('modal-critical-threshold').value = @json(old('critical_threshold', ''));
    });
</script>
@endif
