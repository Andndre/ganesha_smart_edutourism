<script>
// ==========================================
// AR MODEL DRAWER MODAL — functions for map-manager context
// ==========================================
const storageUrlMap = "{{ asset('storage') }}";
let currentModalMarkerCanvasMap = null;
let pendingThumbnailDataMap = null;

function captureModelThumbnailMap() {
    const viewer = document.getElementById('modal-viewer-3d');
    if (!viewer || viewer.classList.contains('hidden') || !viewer.src) return;
    try {
        const canvas = viewer.shadowRoot?.querySelector('canvas');
        if (canvas) {
            pendingThumbnailDataMap = canvas.toDataURL('image/png');
        }
    } catch(e) {
        console.warn('Thumbnail capture failed:', e);
    }
}

function hookModelViewerLoadMap(viewerEl) {
    if (!viewerEl) return;
    viewerEl.addEventListener('load', () => {
        setTimeout(captureModelThumbnailMap, 500);
    }, { once: true });
}

// Global submit handler for model form — called via onsubmit=window.submitModelForm(event)
window.submitModelForm = async function (e) {
    e.preventDefault(); // Must be synchronous before any await — async return value is a Promise (truthy), so onsubmit="return ..." cannot prevent submit
    console.log('[AR-MODEL] submitModelForm called, preventDefault done');

    // Inject thumbnail data if captured
    if (pendingThumbnailDataMap) {
        let input = document.getElementById('thumbnail-data-input');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'thumbnail_data';
            input.id = 'thumbnail-data-input';
            e.target.appendChild(input);
        }
        input.value = pendingThumbnailDataMap;
    }

    const submitBtn = document.getElementById('model-submit-btn');
    if (!submitBtn) return;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
    submitBtn.classList.add('opacity-60', 'cursor-not-allowed');

    try {
        const formData = new FormData(e.target);
        console.log('[AR-MODEL] Fetching:', e.target.action);
        const resp = await fetch(e.target.action, {
            method: 'POST',
            body: formData,
            headers: {'Accept': 'application/json'},
        });
        console.log('[AR-MODEL] Response status:', resp.status, resp.ok);

        if (!resp.ok) {
            const errData = await resp.json().catch(function () {
                return {};
            });
            throw new Error(errData.message || errData.title || 'Gagal menyimpan');
        }

        const data = await resp.json();

        console.log('[AR-MODEL] Response data:', data);
        if (data.success && data.model) {
            console.log('[AR-MODEL] Success! Closing modal and dispatching ar-model-created event');
            closeModelModal();
            window.dispatchEvent(new CustomEvent('ar-model-created', { detail: { model: data.model } }));
            console.log('[AR-MODEL] ar-model-created event dispatched');
        } else {
            throw new Error(data.message || 'Gagal menyimpan');
        }
    } catch (err) {
        console.error('[AR-MODEL] Error caught:', err);
        await Swal.fire({
            title: 'Gagal',
            text: err.message || 'Terjadi kesalahan saat menyimpan model.',
            icon: 'error',
            confirmButtonColor: '#1E5128',
            background: '#ffffff',
        });
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Simpan Aset';
        submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
    }

};

// Attach submit handler via addEventListener — more reliable than onsubmit for async handlers
const _modelForm = document.getElementById('model-form');
console.log('[AR-MODEL] Attaching submit listener to #model-form:', _modelForm ? 'FOUND' : 'NOT FOUND');
if (_modelForm) _modelForm.addEventListener('submit', window.submitModelForm);

function openModelModal() {
    document.getElementById('model-modal-title').innerText = "Tambah Model 3D";
    const form = document.getElementById('model-form');
    form.action = "{{ route('admin.ar-manager.models.store') }}";
    document.getElementById('model-method-container').innerHTML = "";
    document.getElementById('model-field-id').value = "";
    document.getElementById('model-field-name-en').value = "";
    document.getElementById('model-field-name-id').value = "";
    document.getElementById('model-field-marker-id').value = "";
    document.getElementById('model-field-patt-content').value = "";
    if (typeof window.clearAllTiptapEditors === 'function') {
        window.clearAllTiptapEditors(form);
    }
    document.getElementById('model-field-glb-file').value = "";
    document.getElementById('glb-required-asterisk').style.display = 'inline';
    document.getElementById('model-field-usdz-file').value = "";
    document.getElementById('model-field-audio-file-en').value = "";
    document.getElementById('model-field-audio-file-id').value = "";
    document.getElementById('edit-current-audio-en')?.classList.add('hidden');
    document.getElementById('edit-current-audio-id')?.classList.add('hidden');
    document.getElementById('edit-current-glb-container')?.classList.add('hidden');
    document.getElementById('edit-current-usdz-container')?.classList.add('hidden');
    document.getElementById('model-field-tmp-glb').value = '';
    document.getElementById('model-field-tmp-usdz').value = '';
    document.getElementById('modal-marker-preview-wrapper')?.classList.add('hidden');
    currentModalMarkerCanvasMap = null;
    resetModal3DViewerMap();

    window.dispatchEvent(new CustomEvent('open-model-modal'));
}

function closeModelModal() {
    window.dispatchEvent(new CustomEvent('close-model-modal'));
}

function setupModal3DViewerMap(src) {
    const placeholder = document.getElementById('modal-viewer-placeholder');
    const viewer = document.getElementById('modal-viewer-3d');
    if (placeholder) placeholder.classList.add('hidden');
    if (viewer) {
        viewer.classList.remove('hidden');
        viewer.src = src;
        hookModelViewerLoadMap(viewer);
    }
}

function resetModal3DViewerMap() {
    const viewer = document.getElementById('modal-viewer-3d');
    const placeholder = document.getElementById('modal-viewer-placeholder');
    if (viewer) {
        viewer.classList.add('hidden');
        viewer.src = "";
    }
    if (placeholder) placeholder.classList.remove('hidden');
}

function generateARMarkerInModal() {
    const markerInput = document.getElementById('model-field-marker-id');
    const pattInput = document.getElementById('model-field-patt-content');
    const previewWrapper = document.getElementById('modal-marker-preview-wrapper');
    const canvasPlaceholder = document.getElementById('modal-marker-canvas-placeholder');

    if (!markerInput) return;
    const markerId = markerInput.value.trim();

    if (!markerId) {
        previewWrapper?.classList.add('hidden');
        if (pattInput) pattInput.value = '';
        currentModalMarkerCanvasMap = null;
        return;
    }

    previewWrapper?.classList.remove('hidden');

    try {
        const qrValue = `${window.location.origin}/ar/scan/${encodeURIComponent(markerId)}`;
        const qr = new QRious({ value: qrValue, size: 300, level: 'H' });

        const markerCanvas = document.createElement('canvas');
        markerCanvas.width = 500;
        markerCanvas.height = 500;
        const ctx = markerCanvas.getContext('2d');

        ctx.fillStyle = '#000000';
        ctx.fillRect(0, 0, 500, 500);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(100, 100, 300, 300);
        ctx.drawImage(qr.canvas, 100, 100, 300, 300);

        currentModalMarkerCanvasMap = markerCanvas;

        if (canvasPlaceholder) {
            canvasPlaceholder.innerHTML = '';
            const img = document.createElement('img');
            img.src = markerCanvas.toDataURL('image/png');
            img.className = 'w-24 h-24 object-contain';
            canvasPlaceholder.appendChild(img);
        }

        pattInput.value = generatePattText(markerCanvas, 100, 300);

        const logo = new Image();
        logo.onload = function() {
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(212, 212, 76, 76);
            ctx.drawImage(logo, 217, 217, 66, 66);
            currentModalMarkerCanvasMap = markerCanvas;
            if (canvasPlaceholder) {
                const imgEl = canvasPlaceholder.querySelector('img');
                if (imgEl) imgEl.src = markerCanvas.toDataURL('image/png');
            }
            pattInput.value = generatePattText(markerCanvas, 100, 300);
        };
        logo.src = '/icons/logo-penglipuran.png';
    } catch (e) {
        console.error('AR Marker generation failed:', e);
    }
}

function downloadARMarkerFromModal() {
    const markerInput = document.getElementById('model-field-marker-id');
    if (!markerInput || !currentModalMarkerCanvasMap) return;
    const markerId = markerInput.value.trim();
    const link = document.createElement('a');
    link.href = currentModalMarkerCanvasMap.toDataURL('image/png');
    link.download = `${markerId}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Chunked upload init
(function () {
    function initChunkedUpload(inputId, hiddenId, progressId, maxSize, exts) {
        const input = document.getElementById(inputId);
        if (!input) return;
        new ChunkedUploader({
            input: input,
            hiddenInput: document.getElementById(hiddenId),
            progressContainer: document.getElementById(progressId),
            maxSize: maxSize,
            allowedExtensions: exts,
            endpoint: '/admin/api/tus/upload',
            onStart: () => {
                const fileInput = document.getElementById(inputId);
                if (inputId === 'model-field-glb-file' && fileInput?.files[0]) {
                    setupModal3DViewerMap(URL.createObjectURL(fileInput.files[0]));
                }
            },
        });
    }

    initChunkedUpload('model-field-glb-file', 'model-field-tmp-glb', 'model-glb-progress', 20 * 1024 * 1024, ['.glb']);
    initChunkedUpload('model-field-usdz-file', 'model-field-tmp-usdz', 'model-usdz-progress', 50 * 1024 * 1024, ['.usdz']);
})();
</script>
