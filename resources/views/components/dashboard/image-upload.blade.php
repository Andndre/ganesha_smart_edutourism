@props(['id', 'name', 'label', 'multiple' => false, 'existing' => [], 'append' => false, 'maxFiles' => 1, 'maxSizeMb' => 5, 'accept' => 'image/jpeg,image/png,image/webp', 'help' => null, 'existingHint' => null, 'removeName' => null])

@php
    $existingUrls = array_map(
        fn ($path) => \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/', 'images/']) ? asset($path) : asset('storage/'.$path),
        array_values(array_filter((array) $existing)),
    );
@endphp

<div data-image-upload data-max-files="{{ $maxFiles }}" data-max-size="{{ $maxSizeMb * 1024 * 1024 }}" data-append="{{ $append ? 'true' : 'false' }}"
    data-existing-urls="{{ json_encode($existingUrls) }}" {{ $attributes->merge(['class' => 'space-y-2']) }}>
    <div>
        <label for="{{ $id }}" class="text-sm font-bold text-charcoal">{{ $label }}</label>
        <p class="mt-1 text-xs text-gray-500">{{ $help ?? 'JPG, PNG, WebP'.(str_contains($accept, 'image/gif') ? ', atau GIF' : '').' · Maksimal '.$maxSizeMb.' MB per foto'.($multiple ? ' · Hingga '.$maxFiles.' foto' : '') }}</p>
    </div>
    @unless($multiple)
        <div data-single-preview class="relative overflow-hidden rounded-2xl border border-gray-200 bg-surface {{ count($existingUrls) ? '' : 'hidden' }}">
            <img data-single-image src="{{ $existingUrls[0] ?? '' }}" alt="Pratinjau {{ $label }}" class="aspect-video max-h-80 w-full object-cover">
            <div class="absolute inset-x-0 bottom-0 flex flex-wrap items-center justify-between gap-2 bg-gradient-to-t from-charcoal/80 to-transparent p-3 pt-10">
                <span data-single-status class="text-xs font-semibold text-white">Foto tersimpan</span>
                <div class="flex gap-2">
                    <label for="{{ $id }}" class="inline-flex min-h-11 cursor-pointer items-center rounded-xl bg-white px-4 text-xs font-bold text-charcoal">Ganti</label>
                    <button type="button" data-remove-image class="min-h-11 rounded-xl bg-white/90 px-4 text-xs font-bold text-red-700">Hapus</button>
                </div>
            </div>
        </div>
    @endunless
    <div data-drop-zone class="rounded-2xl border-2 border-dashed border-gray-200 bg-surface/70 p-5 text-center transition-colors hover:border-primary/50 {{ !$multiple && count($existingUrls) ? 'hidden' : '' }}">
        <svg class="mx-auto h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V19a2 2 0 002 2h14a2 2 0 002-2v-2.5M12 3v13m0-13 4 4m-4-4L8 7M4 14l3-3 3 3m4 0 3-3 3 3" /></svg>
        <p class="mt-2 text-sm font-semibold text-charcoal">Seret foto ke sini</p>
        <p class="mt-0.5 text-xs text-gray-500">atau pilih dari perangkat</p>
        <label for="{{ $id }}" class="mt-3 inline-flex min-h-11 cursor-pointer items-center justify-center rounded-xl bg-primary px-4 text-xs font-bold text-white transition hover:bg-primary-hover">Pilih Foto</label>
    </div>
    <input id="{{ $id }}" type="file" name="{{ $name }}" accept="{{ $accept }}" @if($multiple) multiple @endif class="sr-only" data-image-input>
    @if($removeName)<input type="hidden" name="{{ $removeName }}" value="0" data-remove-input>@endif
    <p data-image-error class="hidden text-xs font-semibold text-red-700" role="alert"></p>
    @error($name)<p class="text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
    @error(str_replace('[]', '', $name).'.*')<p class="text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
    @if($multiple)
        <div data-existing-section class="{{ count($existingUrls) ? '' : 'hidden' }}"><p class="mb-2 text-xs font-semibold text-gray-500">Foto tersimpan</p><div data-existing-list class="grid grid-cols-3 gap-2 sm:grid-cols-4">@foreach($existingUrls as $url)<img src="{{ $url }}" alt="Foto tersimpan" class="aspect-square w-full rounded-xl border border-gray-200 object-cover">@endforeach</div><p class="mt-2 text-xs text-gray-500">{{ $existingHint ?? 'Foto baru akan menggantikan foto tersimpan.' }}</p></div>
        <div data-image-preview-section class="hidden"><p class="mb-2 text-xs font-semibold text-gray-500">Foto baru</p><div data-image-preview class="grid grid-cols-3 gap-2 sm:grid-cols-4"></div></div>
    @endif
</div>

@pushOnce('scripts', 'dashboard-image-upload-script')
<script>
    function initializeDashboardImageUploads() {
        document.querySelectorAll('[data-image-upload]:not([data-upload-ready])').forEach(upload => {
            upload.dataset.uploadReady = 'true';
            const input = upload.querySelector('[data-image-input]');
            const dropZone = upload.querySelector('[data-drop-zone]');
            const error = upload.querySelector('[data-image-error]');
            const removeInput = upload.querySelector('[data-remove-input]');
            const singlePreview = upload.querySelector('[data-single-preview]');
            const singleImage = upload.querySelector('[data-single-image]');
            const singleStatus = upload.querySelector('[data-single-status]');
            const preview = upload.querySelector('[data-image-preview]');
            const previewSection = upload.querySelector('[data-image-preview-section]');
            const existingSection = upload.querySelector('[data-existing-section]');
            const existingList = upload.querySelector('[data-existing-list]');
            const maxFiles = Number(upload.dataset.maxFiles);
            const maxSize = Number(upload.dataset.maxSize);
            const append = upload.dataset.append === 'true';
            const acceptedTypes = input.accept.split(',').map(type => type.trim()).filter(Boolean);
            let existingUrls = JSON.parse(upload.dataset.existingUrls || '[]');
            let objectUrls = [];

            const showError = message => {
                error.textContent = message;
                error.classList.toggle('hidden', !message);
            };
            const render = () => {
                objectUrls.forEach(url => URL.revokeObjectURL(url));
                objectUrls = [];
                const files = [...input.files];
                if (singlePreview) {
                    const file = files[0];
                    const source = file ? URL.createObjectURL(file) : existingUrls[0];
                    if (file) objectUrls.push(source);
                    singleImage.src = source || '';
                    singleStatus.textContent = file ? 'Foto baru' : 'Foto tersimpan';
                    singlePreview.classList.toggle('hidden', !source);
                    dropZone.classList.toggle('hidden', !!source);
                    return;
                }
                existingList.replaceChildren();
                existingUrls.forEach(url => {
                    const image = document.createElement('img');
                    image.src = url;
                    image.alt = 'Foto tersimpan';
                    image.className = 'aspect-square w-full rounded-xl border border-gray-200 object-cover';
                    existingList.append(image);
                });
                existingSection.classList.toggle('hidden', existingUrls.length === 0);
                preview.replaceChildren();
                files.forEach((file, index) => {
                    const url = URL.createObjectURL(file);
                    objectUrls.push(url);
                    const item = document.createElement('div');
                    item.className = 'relative';
                    const image = document.createElement('img');
                    image.src = url;
                    image.alt = file.name;
                    image.className = 'aspect-square w-full rounded-xl border border-gray-200 object-cover';
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'absolute right-1 top-1 flex min-h-11 min-w-11 items-center justify-center rounded-full bg-charcoal/80 text-lg leading-none text-white';
                    remove.setAttribute('aria-label', `Hapus ${file.name} dari pilihan`);
                    remove.textContent = '×';
                    remove.addEventListener('click', () => {
                        const transfer = new DataTransfer();
                        [...input.files].forEach((selected, selectedIndex) => {
                            if (selectedIndex !== index) transfer.items.add(selected);
                        });
                        input.files = transfer.files;
                        render();
                    });
                    item.append(image, remove);
                    preview.append(item);
                });
                previewSection.classList.toggle('hidden', files.length === 0);
            };
            const selectFiles = files => {
                const selected = [...files];
                const availableSlots = append ? Math.max(0, maxFiles - existingUrls.length) : maxFiles;
                if (selected.length > availableSlots) {
                    showError(`Pilih maksimal ${availableSlots} foto baru.`);
                    input.value = '';
                    render();
                    return;
                }
                if (selected.some(file => !acceptedTypes.includes(file.type) || file.size > maxSize)) {
                    showError(`Gunakan format gambar yang didukung berukuran maksimal ${maxSize / 1024 / 1024} MB.`);
                    input.value = '';
                    render();
                    return;
                }
                showError('');
                const transfer = new DataTransfer();
                selected.forEach(file => transfer.items.add(file));
                input.files = transfer.files;
                if (removeInput && selected.length) removeInput.value = '0';
                render();
            };
            upload.setExistingImages = urls => {
                existingUrls = urls.filter(Boolean);
                input.value = '';
                if (removeInput) removeInput.value = '0';
                showError('');
                render();
            };
            input.addEventListener('change', () => selectFiles(input.files));
            upload.querySelector('[data-remove-image]')?.addEventListener('click', () => {
                input.value = '';
                existingUrls = [];
                if (removeInput) removeInput.value = '1';
                render();
            });
            dropZone.addEventListener('dragover', event => {
                event.preventDefault();
                dropZone.classList.add('border-primary', 'bg-primary/5');
            });
            dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-primary', 'bg-primary/5'));
            dropZone.addEventListener('drop', event => {
                event.preventDefault();
                dropZone.classList.remove('border-primary', 'bg-primary/5');
                selectFiles(event.dataTransfer.files);
            });
            render();
        });
    }
    document.addEventListener('DOMContentLoaded', initializeDashboardImageUploads);
    document.addEventListener('livewire:navigated', initializeDashboardImageUploads);
    if (document.readyState !== 'loading') initializeDashboardImageUploads();
</script>
@endPushOnce
