@props(['id', 'name', 'label', 'multiple' => false, 'existing' => [], 'append' => false, 'maxFiles' => 1, 'maxSizeMb' => 5, 'accept' => 'image/jpeg,image/png,image/webp', 'help' => null, 'existingHint' => null, 'removeName' => null, 'removeExistingName' => null])

@php
    $existingUrls = array_map(
        fn ($path) => \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/']) ? asset($path) : asset('storage/'.$path),
        array_values(array_filter((array) $existing)),
    );
@endphp

<div data-image-upload data-max-files="{{ $maxFiles }}" data-max-size="{{ $maxSizeMb * 1024 * 1024 }}" data-append="{{ $append ? 'true' : 'false' }}"
    data-existing-urls="{{ json_encode($existingUrls) }}" data-existing-paths="{{ json_encode(array_values(array_filter((array) $existing))) }}"
    data-remove-existing-name="{{ $removeExistingName }}" {{ $attributes->merge(['class' => 'min-w-0 space-y-2']) }}>
    <div>
        <label for="{{ $id }}" class="text-sm font-bold text-charcoal">{{ $label }}</label>
        <p class="mt-1 text-xs text-gray-500">{{ $help ?? 'JPG, PNG, WebP'.(str_contains($accept, 'image/gif') ? ', atau GIF' : '').' · Maksimal '.$maxSizeMb.' MB per foto'.($multiple ? ' · Hingga '.$maxFiles.' foto' : '') }}</p>
    </div>
    @unless($multiple)
        <div data-single-preview class="relative overflow-hidden rounded-2xl border border-gray-200 bg-surface {{ count($existingUrls) ? '' : 'hidden' }}">
            <img data-single-image src="{{ $existingUrls[0] ?? '' }}" width="640" height="360" alt="Pratinjau {{ $label }}" class="aspect-video max-h-80 w-full object-cover">
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
        <button type="button" data-pick-images aria-controls="{{ $id }}" class="mt-3 inline-flex min-h-11 items-center justify-center rounded-xl bg-primary px-4 text-xs font-bold text-white transition hover:bg-primary-hover focus-visible:outline-primary">Pilih Foto</button>
    </div>
    <input id="{{ $id }}" type="file" name="{{ $name }}" accept="{{ $accept }}" @if($multiple) multiple @endif class="sr-only" data-image-input>
    @if($removeName)<input type="hidden" name="{{ $removeName }}" value="0" data-remove-input>@endif
    <p data-image-error class="hidden text-xs font-semibold text-red-700" role="alert"></p>
    @error(str_replace('[]', '', $name))<p class="text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
    @error(str_replace('[]', '', $name).'.*')<p class="text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
    @if($removeExistingName)
        @error(str_replace('[]', '', $removeExistingName).'.*')<p class="text-xs font-semibold text-red-700">{{ $message }}</p>@enderror
    @endif
    @if($multiple)
        <div data-existing-section class="{{ count($existingUrls) ? '' : 'hidden' }}">
            <p class="mb-2 text-xs font-semibold text-gray-500">Foto tersimpan</p>
            <div data-existing-list class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">@foreach($existingUrls as $url)<img src="{{ $url }}" width="240" height="180" alt="Foto tersimpan {{ $loop->iteration }}" class="aspect-[4/3] w-full rounded-xl border border-gray-200 object-cover">@endforeach</div>
        </div>
        <div data-image-preview-section class="hidden"><p class="mb-2 text-xs font-semibold text-gray-500">Foto baru</p><div data-image-preview class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"></div></div>
        <div data-image-removals></div>
        <div data-image-undo class="hidden flex-wrap items-center gap-2 rounded-xl bg-amber-50 p-3 text-xs text-amber-900">
            <span data-image-removed-count></span><button type="button" data-undo-removals class="min-h-11 px-2 font-bold underline">Batalkan penghapusan</button>
        </div>
        <p data-image-count class="text-xs font-semibold text-gray-600" aria-live="polite"></p>
        <p class="text-xs text-gray-500">{{ $existingHint ?? ($append ? 'Foto baru akan ditambahkan ke galeri.' : 'Foto baru akan menggantikan foto tersimpan.') }}</p>
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
            const removeExistingName = upload.dataset.removeExistingName;
            const removals = upload.querySelector('[data-image-removals]');
            const undo = upload.querySelector('[data-image-undo]');
            const acceptedTypes = input.accept.split(',').map(type => type.trim()).filter(Boolean);
            let existingUrls = JSON.parse(upload.dataset.existingUrls || '[]');
            let existingPaths = JSON.parse(upload.dataset.existingPaths || '[]');
            let removed = new Set();
            let selectedFiles = [...input.files];
            let objectUrls = [];

            const syncInput = () => {
                const transfer = new DataTransfer();
                selectedFiles.forEach(file => transfer.items.add(file));
                input.files = transfer.files;
                upload.dispatchEvent(new Event('input', { bubbles: true }));
            };
            const photo = (url, label, onRemove) => {
                const item = document.createElement('div');
                item.className = 'relative min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-surface';
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.rel = 'noopener';
                link.className = 'flex aspect-[4/3] items-center justify-center';
                link.setAttribute('aria-label', `Lihat ${label}`);
                const image = document.createElement('img');
                image.alt = label;
                image.width = 240;
                image.height = 180;
                image.className = 'h-full w-full object-cover';
                image.addEventListener('error', () => {
                    image.hidden = true;
                    const fallback = document.createElement('span');
                    fallback.className = 'px-3 text-center text-xs text-gray-500';
                    fallback.textContent = 'Foto tidak dapat dimuat';
                    link.append(fallback);
                }, { once: true });
                image.src = url;
                link.append(image);
                item.append(link);
                if (onRemove) {
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'absolute right-1 top-1 flex min-h-11 min-w-11 items-center justify-center rounded-full bg-charcoal/80 text-xl text-white focus-visible:outline-primary';
                    remove.setAttribute('aria-label', `Hapus ${label}`);
                    remove.textContent = '×';
                    remove.addEventListener('click', () => {
                        onRemove();
                        upload.querySelector('[data-pick-images]').focus();
                    });
                    item.append(remove);
                }
                return item;
            };

            const showError = message => {
                error.textContent = message;
                error.classList.toggle('hidden', !message);
            };
            const render = () => {
                objectUrls.forEach(url => URL.revokeObjectURL(url));
                objectUrls = [];
                const files = selectedFiles;
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
                existingUrls.forEach((url, index) => {
                    if (removed.has(index)) return;
                    existingList.append(photo(url, `foto tersimpan ${index + 1}`, removeExistingName ? () => {
                        removed.add(index);
                        showError('');
                        syncInput();
                        render();
                    } : null));
                });
                existingSection.classList.toggle('hidden', existingUrls.length === removed.size);
                removals.replaceChildren();
                removed.forEach(index => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = removeExistingName;
                    hidden.value = existingPaths[index];
                    removals.append(hidden);
                });
                undo.classList.toggle('hidden', !removed.size);
                undo.classList.toggle('flex', !!removed.size);
                undo.querySelector('[data-image-removed-count]').textContent = `${removed.size} foto akan dihapus setelah disimpan.`;
                preview.replaceChildren();
                files.forEach((file, index) => {
                    const url = URL.createObjectURL(file);
                    objectUrls.push(url);
                    preview.append(photo(url, file.name, () => {
                        selectedFiles.splice(index, 1);
                        showError('');
                        syncInput();
                        render();
                    }));
                });
                previewSection.classList.toggle('hidden', files.length === 0);
                const total = (append || !files.length ? existingUrls.length - removed.size : 0) + files.length;
                upload.querySelector('[data-image-count]').textContent = `${total} / ${maxFiles} foto`;
            };
            const selectFiles = files => {
                const incoming = [...files];
                const selected = input.multiple ? [...selectedFiles] : [];
                incoming.forEach(file => {
                    if (!selected.some(previous => previous.name === file.name && previous.size === file.size && previous.lastModified === file.lastModified)) selected.push(file);
                });
                const availableSlots = append ? Math.max(0, maxFiles - existingUrls.length + removed.size) : maxFiles;
                if (selected.length > availableSlots) {
                    showError(`Pilih maksimal ${availableSlots} foto baru.`);
                    syncInput();
                    return;
                }
                if (selected.some(file => !acceptedTypes.includes(file.type) || file.size > maxSize)) {
                    showError(`Gunakan format gambar yang didukung berukuran maksimal ${maxSize / 1024 / 1024} MB.`);
                    syncInput();
                    return;
                }
                showError('');
                selectedFiles = selected;
                syncInput();
                if (removeInput && selected.length) removeInput.value = '0';
                render();
            };
            upload.setExistingImages = (urls, paths = urls) => {
                existingUrls = urls.filter(Boolean);
                existingPaths = paths.filter(Boolean);
                removed.clear();
                selectedFiles = [];
                input.value = '';
                if (removeInput) removeInput.value = '0';
                showError('');
                render();
            };
            input.addEventListener('change', () => selectFiles(input.files));
            upload.querySelector('[data-pick-images]').addEventListener('click', () => input.click());
            upload.closest('form')?.addEventListener('reset', () => {
                selectedFiles = [];
                removed.clear();
                showError('');
                render();
            });
            upload.querySelector('[data-undo-removals]')?.addEventListener('click', () => {
                if (append && selectedFiles.length + existingUrls.length > maxFiles) {
                    showError('Batalkan beberapa foto baru agar jumlah foto tidak melebihi batas.');
                    return;
                }
                removed.clear();
                showError('');
                syncInput();
                render();
                upload.querySelector('[data-pick-images]').focus();
            });
            upload.querySelector('[data-remove-image]')?.addEventListener('click', () => {
                input.value = '';
                selectedFiles = [];
                existingUrls = [];
                if (removeInput) removeInput.value = '1';
                showError('');
                render();
                upload.querySelector('[data-pick-images]').focus();
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
