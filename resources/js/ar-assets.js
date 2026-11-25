const formStates = new WeakMap();
const assetStates = new WeakMap();

function messageBox(form, errors) {
    form.querySelectorAll('[data-ar-errors]').forEach((el) => el.remove());
    form.querySelectorAll('[aria-invalid="true"]').forEach((el) => el.removeAttribute('aria-invalid'));
    const box = document.createElement('div');
    box.dataset.arErrors = '';
    box.className = 'rounded-xl border border-red-300 bg-red-50 p-4 text-sm text-red-800';
    box.setAttribute('role', 'alert');
    box.tabIndex = -1;
    const title = document.createElement('p');
    title.className = 'font-semibold';
    title.textContent = 'Periksa isian berikut. Perubahan Anda masih tersimpan di form.';
    box.append(title);
    let first;
    for (const [key, messages] of Object.entries(errors)) {
        const name = key.replace(/\.([^.]+)/g, '[$1]');
        const input = [...form.elements].find((el) => el.name === name);
        const line = document.createElement(input ? 'button' : 'p');
        line.textContent = messages.join(' ');
        line.className = 'mt-2 block text-left underline';
        if (input) {
            line.type = 'button';
            input.setAttribute('aria-invalid', 'true');
            line.addEventListener('click', () => {
                const panel = input.closest('[role="tabpanel"], [role="group"][aria-labelledby]');
                if (panel) {
                    if (formStates.get(form)?.setLocale) formStates.get(form).setLocale(panel.id.endsWith('-en') ? 'en' : 'id');
                    else form.querySelector('[aria-controls="' + panel.id + '"]')?.click();
                }
                input.focus();
            });
            first ??= line;
        }
        box.append(line);
    }
    form.prepend(box);
    box.focus();
    if (first) first.click();
}

function initializeAssetSection(section) {
    if (assetStates.has(section)) return;
    const form = section.closest('form');
    if (!form) return;
    const viewer = section.querySelector('[data-asset-viewer]');
    let previewUrl;
    const state = { model: {}, uploads: [], dirty: false };
    assetStates.set(section, state);
    const show = (element, visible) => element?.classList.toggle('hidden', !visible);

    state.set = (model = {}, restore = false) => {
        state.model = model;
        state.dirty = false;
        state.uploads.forEach((u) => u.abort());
        section.querySelectorAll('[data-asset-file]').forEach((input) => { input.disabled = false; if (!restore) input.value = ''; });
        if (!restore) {
            section.querySelectorAll('[data-asset-temp]').forEach((input) => { input.value = ''; });
            section.querySelector('[data-asset-thumbnail]').value = '';
            section.querySelector('[data-asset-pattern]').value = '';
            const marker = section.querySelector('[data-asset-marker]');
            marker.value = model.ar_marker_id || '';
            section.querySelector('[name="remove_ar_model"]').checked = false;
            section.querySelectorAll('[data-asset-progress]').forEach((el) => show(el, false));
        }
        for (const extension of ['glb', 'usdz']) {
            const path = extension === 'glb' ? model.model_3d_path : model.model_3d_usdz_path;
            const pending = section.querySelector('[data-asset-temp="' + extension + '"]').value;
            section.querySelector('[data-asset-current="' + extension + '"]').textContent = pending
                ? 'Unggahan selesai dan siap disimpan.'
                : path ? 'File aktif: ' + path.split('/').pop() : '';
        }
        section.querySelector('[data-asset-status]').textContent = model.id
            ? model.model_3d_path ? 'Model 3D tersedia. Pilih file baru hanya jika ingin menggantinya.' : 'Marker tersimpan. Tambahkan file GLB saat model 3D sudah siap.'
            : 'Belum ada model 3D. Objek budaya tetap dapat disimpan.';
        show(section.querySelector('[data-asset-remove]'), !!model.id);
        show(viewer, !!model.model_3d_path);
        if (model.model_3d_path) viewer.src = '/storage/' + model.model_3d_path;
        else viewer.removeAttribute('src');
        if (model.thumbnail_path) viewer.poster = '/storage/' + model.thumbnail_path;
        else viewer.removeAttribute('poster');
        section.dispatchEvent(new Event('change'));
    };

    for (const extension of ['glb', 'usdz']) {
        const input = section.querySelector('[data-asset-file="' + extension + '"]');
        const temp = section.querySelector('[data-asset-temp="' + extension + '"]');
        const retry = section.querySelector('[data-asset-retry="' + extension + '"]');
        if (window.ChunkedUploader) {
            const uploader = new window.ChunkedUploader({
                input,
                hiddenInput: temp,
                progressContainer: section.querySelector('[data-asset-progress="' + extension + '"]'),
                endpoint: '/admin/api/tus/upload',
                maxSize: (extension === 'glb' ? 10 : 50) * 1024 * 1024,
                allowedExtensions: ['.' + extension],
                onStart: () => { show(retry, false); show(section.querySelector('[data-asset-clear="' + extension + '"]'), true); state.dirty = true; },
                onError: () => show(retry, true),
                onComplete: () => show(retry, uploader.state === 'error'),
            });
            state.uploads.push(uploader);
            const clear = section.querySelector('[data-asset-clear="' + extension + '"]');
            show(clear, !!temp.value);
            clear.addEventListener('click', () => {
                uploader.abort();
                input.value = '';
                input.disabled = false;
                temp.value = '';
                section.querySelector('[data-asset-thumbnail]').value = '';
                show(section.querySelector('[data-asset-progress="' + extension + '"]'), false);
                show(retry, false);
                show(clear, false);
                if (extension === 'glb') {
                    if (previewUrl) URL.revokeObjectURL(previewUrl);
                    if (state.model.model_3d_path) viewer.src = '/storage/' + state.model.model_3d_path;
                    else viewer.removeAttribute('src');
                    show(viewer, !!state.model.model_3d_path);
                }
                const submit = form.querySelector('[type="submit"]');
                if (submit) submit.disabled = state.uploads.some((u) => u.state === 'uploading');
            });
            retry.addEventListener('click', () => { input.disabled = false; uploader.onFileSelect(); });
        }
        input.addEventListener('change', () => {
            state.dirty = true;
            show(section.querySelector('[data-asset-clear="' + extension + '"]'), true);
            if (extension !== 'glb' || !input.files[0]) return;
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(input.files[0]);
            viewer.src = previewUrl;
            show(viewer, true);
        });
    }
    viewer.addEventListener('load', async () => {
        if (!state.dirty || typeof viewer.toDataURL !== 'function') return;
        try {
            section.querySelector('[data-asset-thumbnail]').value = viewer.toDataURL('image/webp', 0.8);
        } catch {
            // Preview capture is optional; the model itself remains usable.
        }
    });
    section.querySelector('[data-asset-marker]').addEventListener('input', () => {
        state.dirty = true;
        show(section.querySelector('[data-asset-qr]'), false);
        section.querySelector('[data-asset-pattern]').value = '';
    });
    const remove = section.querySelector('[name="remove_ar_model"]');
    remove.addEventListener('change', () => {
        section.querySelector('[data-asset-status]').textContent = remove.checked
            ? 'Model 3D akan dihapus setelah Anda menyimpan perubahan.'
            : state.model.id ? 'Model 3D tersedia.' : 'Belum ada model 3D.';
        state.dirty = true;
    });
    state.set(JSON.parse(section.dataset.initialAssets || '{}'), true);

    const marker = section.querySelector('[data-asset-marker]');
    const qr = section.querySelector('[data-asset-qr]');
    const updateQr = () => {
        if (!marker.value.trim() || !window.QRious) return;
        const code = new window.QRious({ value: location.origin + '/ar/scan/' + encodeURIComponent(marker.value.trim()), size: 300, level: 'H' });
        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = 500;
        const context = canvas.getContext('2d');
        context.fillStyle = '#000';
        context.fillRect(0, 0, 500, 500);
        context.drawImage(code.canvas, 100, 100, 300, 300);
        const pattern = section.querySelector('[data-asset-pattern]');
        const markerValue = marker.value.trim();
        const changed = markerValue !== state.model.ar_marker_id || !state.model.ar_marker_patt_path;
        const update = () => {
            qr.href = canvas.toDataURL('image/png');
            if (changed && marker.value.trim() === markerValue) pattern.value = generatePattText(canvas, 100, 300);
        };
        update();
        const logo = new Image();
        logo.onload = () => {
            if (marker.value.trim() !== markerValue) return;
            context.fillStyle = '#fff';
            context.fillRect(212, 212, 76, 76);
            context.drawImage(logo, 217, 217, 66, 66);
            update();
        };
        logo.src = '/icons/logo-penglipuran.png';
        qr.download = 'marker-qr.png';
        show(qr, true);
        qr.classList.add('inline-flex');
    };
    marker.addEventListener('change', updateQr);
    section.addEventListener('change', updateQr);
    updateQr();
}

function initializeForm(form) {
    if (formStates.has(form)) return;
    const state = { dirty: false, saving: false };
    formStates.set(form, state);
    const languageControls = form.querySelector('[data-content-language-controls]');
    if (languageControls) {
        languageControls.hidden = false;
        form.querySelectorAll('[data-localized-field]').forEach((field) => {
            const tabs = field.querySelector('[role="tablist"]');
            tabs?.removeAttribute('role');
            field.querySelectorAll('[role="tab"]').forEach((button) => { button.hidden = true; });
            field.querySelectorAll('[role="tabpanel"]').forEach((panel) => {
                panel.setAttribute('role', 'group');
                panel.setAttribute('aria-label', field.dataset.fieldLabel + (panel.id.endsWith('-en') ? ' bahasa Inggris' : ' bahasa Indonesia'));
            });
        });
        state.setLocale = (locale) => {
            form.querySelectorAll('.mini-audio-el').forEach((audio) => {
                audio.pause();
                audio.closest('[id]')?.querySelector('.mini-audio-play')?.classList.remove('hidden');
                audio.closest('[id]')?.querySelector('.mini-audio-pause')?.classList.add('hidden');
            });
            form.querySelectorAll('[data-localized-field]').forEach((field) => {
                field.querySelector('[role="tab"][aria-controls$="-panel-' + locale + '"]')?.click();
            });
            languageControls.querySelectorAll('[data-content-locale]').forEach((button) => {
                const active = button.dataset.contentLocale === locale;
                button.setAttribute('aria-pressed', String(active));
                button.classList.toggle('bg-primary', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('text-gray-600', !active);
            });
        };
        languageControls.querySelectorAll('[data-content-locale]').forEach((button) => {
            button.addEventListener('click', () => state.setLocale(button.dataset.contentLocale));
        });
        requestAnimationFrame(() => state.setLocale('id'));
    }

    form.addEventListener('input', () => { state.dirty = true; });
    form.addEventListener('change', () => { state.dirty = true; });
    form.addEventListener('reset', () => { state.dirty = false; });
    form.addEventListener('submit', async (event) => {
        if (event.defaultPrevented) return;
        event.preventDefault();
        if (state.saving) return;
        if ((form._tusUploaders || []).some((u) => ['uploading', 'error'].includes(u.state))) {
            messageBox(form, { model_3d_file: ['Selesaikan unggahan atau coba unggah lagi sebelum menyimpan.'] });
            return;
        }
        state.saving = true;
        const button = form.querySelector('[type="submit"]');
        const label = button?.textContent;
        if (button) { button.disabled = true; button.textContent = 'Menyimpan…'; }
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                messageBox(form, data.errors || { form: [data.message || 'Gagal menyimpan. Periksa koneksi dan coba lagi.'] });
                return;
            }
            state.dirty = false;
            window.location.assign(response.redirected ? response.url : window.location.href);
        } catch {
            messageBox(form, { form: ['Koneksi terputus. Perubahan masih ada di form; coba simpan lagi.'] });
        } finally {
            state.saving = false;
            if (button) { button.disabled = false; button.textContent = label; }
            form.querySelectorAll('[data-asset-file]').forEach((input) => { input.disabled = false; });
        }
    });
}

function initialize() {
    document.querySelectorAll('[data-ar-assets]').forEach(initializeAssetSection);
    document.querySelectorAll('form').forEach((form) => {
        if (form.querySelector('[data-ar-assets]')) initializeForm(form);
    });
    if (window.location.hash === '#model-3d') {
        const section = document.getElementById('model-3d');
        if (section) requestAnimationFrame(() => {
            section.scrollIntoView({ block: 'start' });
            section.querySelector('[data-asset-file="glb"]')?.focus({ preventScroll: true });
        });
    }
}

document.addEventListener('DOMContentLoaded', initialize);
document.addEventListener('livewire:navigated', initialize);
if (document.readyState === 'complete') initialize();
window.addEventListener('ar-model-load', (event) => {
    const section = document.querySelector('#form-cultural [data-ar-assets]');
    if (section) assetStates.get(section)?.set(event.detail.model || {});
    const form = section?.closest('form');
    if (form && formStates.has(form)) formStates.get(form).dirty = false;
});
window.addEventListener('ar-model-reset', () => {
    const section = document.querySelector('#form-cultural [data-ar-assets]');
    if (section) assetStates.get(section)?.set({});
});
window.addEventListener('beforeunload', (event) => {
    const dirty = [...document.querySelectorAll('form')].some((form) => formStates.get(form)?.dirty);
    if (dirty) { event.preventDefault(); event.returnValue = ''; }
});

function generatePattText(canvas, borderWidth, patternSize) {
    const ctx = canvas.getContext('2d');
    const gridSize = 16;
    const cellW = patternSize / gridSize;
    const cellH = patternSize / gridSize;

    const grid = [];
    for (let r = 0; r < gridSize; r++) {
        grid[r] = [];
        for (let c = 0; c < gridSize; c++) {
            const startX = borderWidth + c * cellW;
            const startY = borderWidth + r * cellH;

            const imgData = ctx.getImageData(startX, startY, cellW, cellH);
            const data = imgData.data;
            let sumR = 0, sumG = 0, sumB = 0;
            const count = data.length / 4;

            for (let i = 0; i < data.length; i += 4) {
                sumR += data[i];
                sumG += data[i + 1];
                sumB += data[i + 2];
            }

            const normR = (sumR / count / 255).toFixed(3);
            const normG = (sumG / count / 255).toFixed(3);
            const normB = (sumB / count / 255).toFixed(3);

            grid[r][c] = `${normR} ${normG} ${normB}`;
        }
    }

    const rotations = [];

    function rotate90(arr) {
        const n = arr.length;
        const rotated = Array.from({ length: n }, () => []);
        for (let r = 0; r < n; r++) {
            for (let c = 0; c < n; c++) {
                rotated[c][n - 1 - r] = arr[r][c];
            }
        }
        return rotated;
    }

    let currentGrid = grid;
    for (let i = 0; i < 4; i++) {
        const blockLines = [];
        for (let r = 0; r < gridSize; r++) {
            blockLines.push(currentGrid[r].join(' '));
        }
        rotations.push(blockLines.join('\n'));
        currentGrid = rotate90(currentGrid);
    }

    return rotations.join('\n\n') + '\n';
}

document.addEventListener('livewire:navigate', (event) => {
    const dirty = [...document.querySelectorAll('form')].some((form) => formStates.get(form)?.dirty);
    if (dirty && !window.confirm('Perubahan belum disimpan. Tinggalkan halaman ini?')) event.preventDefault();
});
