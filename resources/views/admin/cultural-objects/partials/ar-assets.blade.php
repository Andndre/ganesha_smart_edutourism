@php
    $assetModel = $assetModel ?? null;
    $assetPrefix = $assetPrefix ?? 'cultural';
    $assetData = $assetModel?->only(['id', 'model_3d_path', 'model_3d_usdz_path', 'thumbnail_path', 'ar_marker_id', 'ar_marker_patt_path']) ?? [];
@endphp
<section id="model-3d" tabindex="-1" data-ar-assets data-initial-assets="{{ json_encode($assetData) }}" class="scroll-mt-24 space-y-4" aria-label="Model 3D dan AR">
    <div>
        <h2 class="font-display text-lg font-bold text-charcoal">Model 3D dan AR <span class="font-sans text-sm font-normal text-gray-500">(opsional)</span></h2>
        <p class="mt-1 text-sm leading-relaxed text-gray-600">Tampilan 3D dan AR menggunakan nama, deskripsi, dan audio objek budaya ini. Untuk model tambahan, buat subobjek budaya.</p>
    </div>
    <p data-asset-status role="status" class="rounded-xl bg-surface px-4 py-3 text-sm text-gray-600">Belum ada model 3D. Objek budaya tetap dapat disimpan.</p>
    <model-viewer data-asset-viewer camera-controls touch-action="pan-y" alt="Pratinjau model 3D objek budaya" class="hidden h-56 w-full rounded-xl bg-surface"></model-viewer>
    <input type="hidden" name="ar_marker_patt_content" data-asset-pattern>
    <input type="hidden" name="thumbnail_data" data-asset-thumbnail>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach (['glb' => ['File GLB', 'model_3d_file', 'tmp_model_3d_path', '10 MB'], 'usdz' => ['File USDZ untuk iOS', 'model_3d_usdz_file', 'tmp_model_3d_usdz_path', '50 MB']] as $extension => [$label, $fileField, $tempField, $limit])
            <div class="min-w-0">
                <label for="{{ $assetPrefix }}-{{ $extension }}" class="block text-sm font-semibold text-charcoal">{{ $label }}</label>
                <p id="{{ $assetPrefix }}-{{ $extension }}-help" class="mb-2 mt-1 text-sm text-gray-500">{{ $extension === 'glb' ? 'Diperlukan saat menambahkan model.' : 'Opsional untuk AR Quick Look.' }} Maksimal {{ $limit }}.</p>
                <input type="file" id="{{ $assetPrefix }}-{{ $extension }}" name="{{ $fileField }}" accept=".{{ $extension }}"
                    data-asset-file="{{ $extension }}" aria-describedby="{{ $assetPrefix }}-{{ $extension }}-help"
                    class="block min-h-11 w-full min-w-0 cursor-pointer rounded-xl border border-gray-200 bg-surface/60 text-sm text-gray-600 file:mr-3 file:min-h-11 file:cursor-pointer file:border-0 file:bg-primary/10 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-primary hover:file:bg-primary/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary disabled:cursor-not-allowed disabled:opacity-60">
                <input type="hidden" name="{{ $tempField }}" data-asset-temp="{{ $extension }}" value="{{ old($tempField) }}">
                <p data-asset-current="{{ $extension }}" class="mt-2 break-all text-sm text-gray-600"></p>
                <div data-asset-progress="{{ $extension }}" class="tus-progress-container mt-2 hidden" role="status" aria-live="polite">
                    <span class="tus-status-icon"></span>
                    <span class="tus-progress-text text-sm text-gray-600"></span>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-200"><div class="tus-progress-bar h-full bg-primary" style="width:0%"></div></div>
                </div>
                <button type="button" data-asset-retry="{{ $extension }}" class="mt-2 hidden min-h-11 text-sm font-semibold text-primary underline">Coba unggah lagi</button>
                <button type="button" data-asset-clear="{{ $extension }}" class="mt-2 hidden min-h-11 text-sm font-semibold text-gray-600 underline">Batalkan unggahan baru</button>
                @error($fileField)<p class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
                @error($tempField)<p class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
    <div>
        <label for="{{ $assetPrefix }}-marker" class="block text-sm font-semibold text-charcoal">ID marker AR</label>
        <p id="{{ $assetPrefix }}-marker-help" class="mb-2 mt-1 text-sm text-gray-500">Opsional dan harus unik. Mengganti file model tidak mengubah marker ini.</p>
        <input id="{{ $assetPrefix }}-marker" name="ar_marker_id" data-asset-marker value="{{ old('ar_marker_id', $assetModel?->ar_marker_id) }}"
            aria-describedby="{{ $assetPrefix }}-marker-help" autocomplete="off" spellcheck="false"
            class="min-h-11 w-full rounded-xl border border-gray-300 px-4 py-2.5 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
        @error('ar_marker_id')<p class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
        <a data-asset-qr download class="mt-2 hidden min-h-11 items-center text-sm font-semibold text-primary underline">Unduh QR marker</a>
    </div>
    <label data-asset-remove class="hidden flex min-h-11 w-full cursor-pointer items-start gap-3 rounded-xl border border-gray-200 bg-surface/60 p-4 transition-colors hover:border-red-300 has-checked:border-red-300 has-checked:bg-red-50">
        <input type="checkbox" name="remove_ar_model" value="1" @checked(old('remove_ar_model')) aria-describedby="{{ $assetPrefix }}-remove-help">
        <span class="min-w-0 flex-1 text-sm leading-relaxed text-charcoal"><span class="block font-semibold">Hapus model 3D saat disimpan</span><span id="{{ $assetPrefix }}-remove-help" class="mt-1 block text-gray-600">File 3D dan marker dihapus. Nama, deskripsi, serta audio objek budaya tetap tersimpan.</span></span>
    </label>
    @error('remove_ar_model')<p class="text-sm text-red-700" role="alert">{{ $message }}</p>@enderror
</section>

@once
    @push('scripts')
        <script type="module" src="{{ asset('js/model-viewer.min.js') }}"></script>
    @endpush
@endonce
