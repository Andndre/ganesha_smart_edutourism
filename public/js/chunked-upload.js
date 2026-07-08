/**
 * ChunkedUploader — wraps tus-js-client for admin file uploads.
 *
 * Usage:
 *   new ChunkedUploader({
 *     input: document.getElementById('my-file-input'),
 *     hiddenInput: document.getElementById('my-temp-uuid'),
 *     progressContainer: document.getElementById('my-progress'),
 *     maxSize: 20 * 1024 * 1024,  // 20MB
 *     allowedExtensions: ['.glb'],
 *     onStart: () => { document.getElementById('submit-btn').disabled = true; },
 *     onComplete: () => { document.getElementById('submit-btn').disabled = false; },
 *     endpoint: '/admin/api/tus/upload',
 *   });
 */
class ChunkedUploader {
    constructor(opts) {
        this.opts = opts;
        this.upload = null;
        this.state = 'idle'; // idle | uploading | complete | error

        this.progressBar = opts.progressContainer?.querySelector('.tus-progress-bar');
        this.progressText = opts.progressContainer?.querySelector('.tus-progress-text');
        this.statusIcon = opts.progressContainer?.querySelector('.tus-status-icon');

        // Find submit button — explicit or first [type="submit"] in form
        const form = opts.progressContainer?.closest('form');
        this.submitBtn = opts.submitButton || form?.querySelector('[type="submit"]');

        // Track all uploaders on this form for cross-upload coordination
        if (form) {
            if (!form._tusUploaders) form._tusUploaders = [];
            form._tusUploaders.push(this);
            form.addEventListener('submit', (e) => {
                const anyUploading = form._tusUploaders.some(u => u.state === 'uploading');
                if (anyUploading) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Upload Masih Berlangsung',
                        text: 'Harap tunggu hingga semua file selesai diupload.',
                        icon: 'warning',
                        confirmButtonColor: '#1E5128',
                        confirmButtonText: 'Mengerti',
                        background: '#ffffff'
                    });
                }
            });
        }

        opts.input.addEventListener('change', () => this.onFileSelect());
    }

    onFileSelect() {
        const file = this.opts.input.files[0];
        if (!file) return;

        // Validate extension
        const ext = '.' + file.name.split('.').pop().toLowerCase();
        if (this.opts.allowedExtensions && !this.opts.allowedExtensions.includes(ext)) {
            this.showError('Tipe file tidak diizinkan. Gunakan: ' + this.opts.allowedExtensions.join(', '));
            this.opts.input.value = '';
            return;
        }

        // Validate size
        if (file.size > this.opts.maxSize) {
            const maxMB = Math.round(this.opts.maxSize / 1024 / 1024);
            this.showError('Ukuran file maksimal ' + maxMB + 'MB.');
            this.opts.input.value = '';
            return;
        }

        this.startUpload(file);
    }

    startUpload(file) {
        this.abort(); // abort any previous upload

        this.state = 'uploading';
        this.opts.progressContainer?.classList.remove('hidden');
        this.showProgress(0, file.size);
        this.opts.hiddenInput.value = '';
        this.disableSubmit(true);
        this.opts.onStart?.();

        this.upload = new tus.Upload(file, {
            endpoint: this.opts.endpoint,
            chunkSize: 5 * 1024 * 1024, // 5MB chunks
            retryDelays: [0, 3000, 10000],
            metadata: {
                // Unique temp name per upload: tus-php stores the temp file under this
                // name, so two uploads of the same file (e.g. one video for both EN & ID
                // slots) would otherwise share one temp file and append-corrupt it.
                filename: crypto.randomUUID() + '.' + file.name.split('.').pop(),
                filetype: file.type,
            },
            onProgress: (bytesSent, bytesTotal) => {
                this.showProgress(bytesSent, bytesTotal);
            },
            onSuccess: () => {
                // upload.url = "http://.../admin/api/tus/upload/abc123"
                const uuid = this.upload.url.split('/').pop();
                const ext = file.name.split('.').pop();
                this.opts.hiddenInput.value = uuid + '.' + ext;
                this.state = 'complete';
                this.showComplete(file.name);
                this.disableSubmit(false);
                this.opts.onComplete?.(uuid + '.' + ext);
            },
            onError: (error) => {
                this.state = 'error';
                this.showError('Upload gagal: ' + (error.message || 'Unknown error'));
                this.disableSubmit(false);
                this.opts.onError?.(error);
            },
        });

        this.upload.start();
    }

    abort() {
        if (this.upload) {
            this.upload.abort();
            this.upload = null;
        }
        this.state = 'idle';
    }

    showProgress(sent, total) {
        const pct = Math.round((sent / total) * 100);
        const sentMB = (sent / 1024 / 1024).toFixed(1);
        const totalMB = (total / 1024 / 1024).toFixed(1);
        if (this.progressBar) {
            this.progressBar.style.width = pct + '%';
        }
        if (this.progressText) {
            this.progressText.textContent = sentMB + 'MB / ' + totalMB + 'MB (' + pct + '%)';
        }
        if (this.statusIcon) {
            this.statusIcon.innerHTML = '<svg class="h-4 w-4 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
        }
    }

    showComplete(filename) {
        if (this.progressBar) this.progressBar.style.width = '100%';
        if (this.progressText) this.progressText.textContent = '✓ ' + filename;
        if (this.statusIcon) {
            this.statusIcon.innerHTML = '<svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>';
        }
        this.opts.progressContainer?.classList.remove('tus-error');
        this.opts.progressContainer?.classList.add('tus-complete');
    }

    showError(msg) {
        if (this.progressText) this.progressText.textContent = '✗ ' + msg;
        if (this.statusIcon) {
            this.statusIcon.innerHTML = '<svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>';
        }
        if (this.progressBar) this.progressBar.style.width = '0%';
        this.opts.progressContainer?.classList.add('tus-error');
        this.state = 'error';
        this.opts.onComplete?.();
    }

    disableSubmit(disabled) {
        if (this.submitBtn) {
            this.submitBtn.disabled = disabled;
            this.submitBtn.classList.toggle('opacity-50', disabled);
            this.submitBtn.classList.toggle('cursor-not-allowed', disabled);
        }
    }
}
