# Chunked Upload with Progress Bar Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add chunked file upload with progress bar for GLB/USDZ files in AR Manager and UMKM Category admin forms via tus-php + tus-js-client.

**Architecture:** Tus server (tus-php) handles chunked upload protocol. Client chunks file via tus-js-client. On complete, UUID stored in hidden input. Form submit triggers TempToFinal file move. Temp files cleaned daily via artisan command.

**Tech Stack:** ankitpokhrel/tus-php v3, tus-js-client v4 (CDN), Alpine.js (existing)

## Global Constraints

- All tus routes behind `auth` + `admin` middleware
- CSRF exempt for tus routes (tus uses PATCH/POST/DELETE without CSRF token)
- Max upload 50MB (tus-php config)
- Temp dir: `storage/app/tus/temp/`
- Cache dir for tus: `storage/app/tus/cache/` (file-based)
- Temp expiry: 24 hours (tus config) + cron cleanup

---

### Task 1: Install tus-php + Create Server + Service + Cleanup Command

**Files:**
- Create: `app/Services/TusService.php`
- Create: `app/Http/Controllers/Api/TusController.php`
- Create: `app/Console/Commands/CleanupTusTemp.php`
- Modify: `routes/web.php` (add tus routes)
- Modify: `routes/console.php` (schedule cleanup)
- Modify: `app/Http/Middleware/VerifyCsrfToken.php` (except tus routes)

- [ ] **Step 1: Install tus-php**

```bash
cd /home/andndre/Code/ganesha_smart_edutourism
composer require ankitpokhrel/tus-php:^3.0
```

No config publish needed — we'll configure inline.

- [ ] **Step 2: Create TusService**

File: `app/Services/TusService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TusService
{
    /**
     * Move file from tus temp to final storage.
     *
     * @param string $tempKey  e.g. "abc123.glb" (uuid.extension)
     * @param string $destDir  e.g. "models" or "models_usdz"
     * @param string|null $customFilename  optional override (e.g. Str::random(40).'.usdz')
     * @return string  relative path from public disk root
     */
    public static function moveFromTemp(string $tempKey, string $destDir, ?string $customFilename = null): string
    {
        $parts = explode('.', $tempKey);
        $uuid = $parts[0];
        $ext = $parts[1] ?? 'glb';

        $tempPath = storage_path('app/tus/temp/' . $uuid);
        if (! file_exists($tempPath)) {
            throw new \RuntimeException("Temp file not found: {$uuid}");
        }

        $finalName = $customFilename ?: Str::random(40) . '.' . $ext;
        $destPath = $destDir . '/' . $finalName;

        $storage = Storage::disk('public');
        $storage->put($destPath, file_get_contents($tempPath));

        // Remove temp file
        unlink($tempPath);

        // Remove tus cache entry if exists
        $cacheFile = storage_path('app/tus/cache/' . $uuid);
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        return $destPath;
    }

    /**
     * Get temp upload dir.
     */
    public static function tempDir(): string
    {
        return storage_path('app/tus/temp');
    }

    /**
     * Clean up temp files older than $hours.
     */
    public static function cleanTemp(int $hours = 24): int
    {
        $count = 0;
        $dir = self::tempDir();
        if (! is_dir($dir)) {
            return 0;
        }

        $expire = now()->subHours($hours)->timestamp;

        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $dir . '/' . $file;
            if (filemtime($path) < $expire) {
                unlink($path);
                $count++;
            }
        }

        // Clean cache entries too
        $cacheDir = storage_path('app/tus/cache');
        if (is_dir($cacheDir)) {
            foreach (scandir($cacheDir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $cacheDir . '/' . $file;
                if (filemtime($path) < $expire) {
                    unlink($path);
                }
            }
        }

        return $count;
    }
}
```

- [ ] **Step 3: Create TusController**

File: `app/Http/Controllers/Api/TusController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use TusPhp\Tus\Server;
use TusPhp\Cache\FileStore;

class TusController extends Controller
{
    public function handle()
    {
        $server = new Server('file');

        // Ensure dirs exist
        $uploadDir = storage_path('app/tus/temp');
        $cacheDir = storage_path('app/tus/cache');
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $server->setApiPath('/api/tus/upload');
        $server->setUploadDir($uploadDir);
        $server->setCache(new FileStore($cacheDir));
        $server->setMaxUploadSize(52428800); // 50MB

        // TUS-UPLOAD-EXPIRY: 24 hours
        $server->setConfig(['TUS-UPLOAD-EXPIRY' => 86400]);

        $response = $server->serve();

        // Convert Symfony response to Laravel response
        return response(
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->all()
        );
    }
}
```

- [ ] **Step 4: Add tus routes to web.php**

Add inside the `prefix('admin')` group (after existing admin routes):

```php
// Tus chunked upload — handles POST (create), PATCH (chunk), HEAD, DELETE, OPTIONS
// CSRF exempt via VerifyCsrfToken except array
Route::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], '/api/tus/upload', [\App\Http\Controllers\Api\TusController::class, 'handle'])
    ->middleware(['auth', 'admin']);
Route::match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], '/api/tus/upload/{any}', [\App\Http\Controllers\Api\TusController::class, 'handle'])
    ->middleware(['auth', 'admin'])
    ->where('any', '.*');
```

- [ ] **Step 5: Exclude tus routes from CSRF protection**

Read `app/Http/Middleware/VerifyCsrfToken.php` and add to `$except`:

```php
protected $except = [
    'admin/api/tus/upload',
    'admin/api/tus/upload/*',
];
```

(The route prefix is `admin/` because it's inside the admin prefix group.)

- [ ] **Step 6: Create CleanupTusTemp command**

```bash
cd /home/andndre/Code/ganesha_smart_edutourism
php artisan make:command CleanupTusTemp
```

File: `app/Console/Commands/CleanupTusTemp.php`

```php
<?php

namespace App\Console\Commands;

use App\Services\TusService;
use Illuminate\Console\Command;

class CleanupTusTemp extends Command
{
    protected $signature = 'app:cleanup-tus';
    protected $description = 'Delete expired tus temp upload files older than 24 hours';

    public function handle()
    {
        $count = TusService::cleanTemp(24);
        $this->info("Cleaned {$count} expired temp files.");
    }
}
```

- [ ] **Step 7: Schedule cleanup in console.php**

Add to `routes/console.php`:

```php
Schedule::command('app:cleanup-tus')->daily();
```

- [ ] **Step 8: Create tus temp dir structure**

```bash
mkdir -p /home/andndre/Code/ganesha_smart_edutourism/storage/app/tus/temp
mkdir -p /home/andndre/Code/ganesha_smart_edutourism/storage/app/tus/cache
touch /home/andndre/Code/ganesha_smart_edutourism/storage/app/tus/temp/.gitkeep
touch /home/andndre/Code/ganesha_smart_edutourism/storage/app/tus/cache/.gitkeep
```

- [ ] **Step 9: Wire up tus-js-client CDN in layouts**

Add to `resources/views/layouts/dashboard.blade.php` before `@stack('scripts')` or in the `<head>`:

```html
<script src="https://cdn.jsdelivr.net/npm/tus-js-client@4/dist/tus.min.js"></script>
```

(This makes `tus` available globally for all admin pages.)

---

### Task 2: Create Client-Side ChunkedUploader Class

**Files:**
- Create: `resources/js/chunked-upload.js`

This is a plain JS class (no build step needed — loaded via `<script>` tag or inline).

- [ ] **Step 1: Create chunked-upload.js**

File: `resources/js/chunked-upload.js`

```js
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
        this.opts.onStart?.();

        this.upload = new tus.Upload(file, {
            endpoint: this.opts.endpoint,
            chunkSize: 5 * 1024 * 1024, // 5MB chunks
            retryDelays: [0, 3000, 10000],
            metadata: {
                filename: file.name,
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
                this.opts.onComplete?.(uuid + '.' + ext);
            },
            onError: (error) => {
                this.state = 'error';
                this.showError('Upload gagal: ' + (error.message || 'Unknown error'));
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
}
```

- [ ] **Step 2: Load chunked-upload.js in dashboard layout**

Add to `resources/views/layouts/dashboard.blade.php` after the tus CDN script:

```html
<script src="{{ asset('js/chunked-upload.js') }}"></script>
```

And add chunked-upload to the public directory:

```bash
cp /home/andndre/Code/ganesha_smart_edutourism/resources/js/chunked-upload.js /home/andndre/Code/ganesha_smart_edutourism/public/js/chunked-upload.js
```

(No build step — plain JS, served directly.)

---

### Task 3: Modify AR Manager — Form + Controller

**Files:**
- Modify: `resources/views/admin/ar-manager/partials/modal-form.blade.php`
- Modify: `resources/views/admin/ar-manager/index.blade.php`
- Modify: `app/Http/Controllers/Admin/ARManagerController.php`

- [ ] **Step 1: Add progress bar UI + hidden inputs to modal-form.blade.php**

After each file input in `modal-form.blade.php`, add:

For GLB (after line 100, before the `@error`):
```html
<input type="hidden" name="tmp_model_3d_path" id="model-field-tmp-glb" value="">
<div id="model-glb-progress" class="mt-2 hidden tus-progress-container">
    <div class="flex items-center gap-2">
        <span class="tus-status-icon"></span>
        <span class="tus-progress-text flex-1 text-[10px] text-gray-500"></span>
    </div>
    <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-gray-200">
        <div class="tus-progress-bar h-full rounded-full bg-penglipuran-green transition-all duration-300" style="width:0%"></div>
    </div>
</div>
```

For USDZ (after line 113):
```html
<input type="hidden" name="tmp_model_3d_usdz_path" id="model-field-tmp-usdz" value="">
<div id="model-usdz-progress" class="mt-2 hidden tus-progress-container">
    <!-- same structure as GLB progress -->
    <div class="flex items-center gap-2">
        <span class="tus-status-icon"></span>
        <span class="tus-progress-text flex-1 text-[10px] text-gray-500"></span>
    </div>
    <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-gray-200">
        <div class="tus-progress-bar h-full rounded-full bg-penglipuran-green transition-all duration-300" style="width:0%"></div>
    </div>
</div>
```

For AUDIO (after line 127):
```html
<input type="hidden" name="tmp_audio_narration_path" id="model-field-tmp-audio" value="">
<div id="model-audio-progress" class="mt-2 hidden tus-progress-container">
    <!-- same structure -->
</div>
```

Remove `onchange="previewModelGLB(this)"`, `onchange="previewModelUSDZ(this)"`, `onchange="previewModelAudio(this)"` from file inputs — these will be handled by ChunkedUploader.

Remove the `required` attribute from the GLB input (it will be validated on server side).

- [ ] **Step 2: Add progress bar styles to index.blade.php**

Add to the `<style>` section:

```css
.tus-progress-container.tus-error .tus-progress-bar {
    @apply bg-red-500;
}
.tus-progress-container.tus-complete .tus-progress-bar {
    @apply bg-green-500;
}
```

- [ ] **Step 3: Wire up ChunkedUploader in index.blade.php**

After `previewModelAudio` function (around line 293), add:

```js
// Chunked upload initialization
document.addEventListener('DOMContentLoaded', () => {
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
        });
    }

    // GLB
    initChunkedUpload('model-field-glb-file', 'model-field-tmp-glb', 'model-glb-progress', 20 * 1024 * 1024, ['.glb']);
    // USDZ
    initChunkedUpload('model-field-usdz-file', 'model-field-tmp-usdz', 'model-usdz-progress', 50 * 1024 * 1024, ['.usdz']);
    // Audio
    initChunkedUpload('model-field-audio-file', 'model-field-tmp-audio', 'model-audio-progress', 10 * 1024 * 1024, ['.mp3', '.ogg', '.wav']);
});
```

Also in `openModelEditModal()`, clear tmp hidden inputs when opening edit:
```js
document.getElementById('model-field-tmp-glb').value = '';
document.getElementById('model-field-tmp-usdz').value = '';
document.getElementById('model-field-tmp-audio').value = '';
```

And in `openModelModal()`:
```js
// Add to the reset section
document.getElementById('model-field-tmp-glb').value = '';
document.getElementById('model-field-tmp-usdz').value = '';
document.getElementById('model-field-tmp-audio').value = '';
```

- [ ] **Step 4: Update ARManagerController storeModel()**

Change file handling to check temp UUID first:

```php
// Replace lines 62-73 with:
if ($tmpUuid = $request->input('tmp_model_3d_path')) {
    $modelData['model_3d_path'] = \App\Services\TusService::moveFromTemp($tmpUuid, 'models');
} elseif ($request->hasFile('model_3d_file')) {
    $modelData['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
}

if ($tmpUuid = $request->input('tmp_model_3d_usdz_path')) {
    $modelData['model_3d_usdz_path'] = \App\Services\TusService::moveFromTemp(
        $tmpUuid, 'models_usdz', Str::random(40).'.usdz'
    );
} elseif ($request->hasFile('model_3d_usdz_file')) {
    $file = $request->file('model_3d_usdz_file');
    $modelData['model_3d_usdz_path'] = $file->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
}

if ($tmpUuid = $request->input('tmp_audio_narration_path')) {
    $modelData['audio_narration_path'] = \App\Services\TusService::moveFromTemp($tmpUuid, 'audio');
} elseif ($request->hasFile('audio_narration_file')) {
    $modelData['audio_narration_path'] = $request->file('audio_narration_file')->store('audio', 'public');
}
```

- [ ] **Step 5: Update ARManagerController updateModel()**

Similarly replace file update logic (lines 126-146):

```php
// GLB
if ($tmpUuid = $request->input('tmp_model_3d_path')) {
    if ($model->model_3d_path) {
        Storage::disk('public')->delete($model->model_3d_path);
    }
    $model->model_3d_path = TusService::moveFromTemp($tmpUuid, 'models');
} elseif ($request->hasFile('model_3d_file')) {
    if ($model->model_3d_path) {
        Storage::disk('public')->delete($model->model_3d_path);
    }
    $model->model_3d_path = $request->file('model_3d_file')->store('models', 'public');
}

// USDZ
if ($tmpUuid = $request->input('tmp_model_3d_usdz_path')) {
    if ($model->model_3d_usdz_path) {
        Storage::disk('public')->delete($model->model_3d_usdz_path);
    }
    $model->model_3d_usdz_path = TusService::moveFromTemp($tmpUuid, 'models_usdz', Str::random(40).'.usdz');
} elseif ($request->hasFile('model_3d_usdz_file')) {
    if ($model->model_3d_usdz_path) {
        Storage::disk('public')->delete($model->model_3d_usdz_path);
    }
    $model->model_3d_usdz_path = $request->file('model_3d_usdz_file')
        ->storeAs('models_usdz', Str::random(40).'.usdz', 'public');
}

// Audio
if ($tmpUuid = $request->input('tmp_audio_narration_path')) {
    if ($model->audio_narration_path) {
        Storage::disk('public')->delete($model->audio_narration_path);
    }
    $model->audio_narration_path = TusService::moveFromTemp($tmpUuid, 'audio');
} elseif ($request->hasFile('audio_narration_file')) {
    if ($model->audio_narration_path) {
        Storage::disk('public')->delete($model->audio_narration_path);
    }
    $model->audio_narration_path = $request->file('audio_narration_file')->store('audio', 'public');
}
```

Also add `use App\Services\TusService;` at the top of the file.

---

### Task 4: Modify UMKM Categories — Form + Controller

**Files:**
- Modify: `resources/views/admin/umkm/categories.blade.php`
- Modify: `app/Http/Controllers/Admin/UmkmCategoryController.php`

- [ ] **Step 1: Add progress bar UI + hidden inputs to categories.blade.php**

After GLB file input (after line 207):
```html
<input type="hidden" name="tmp_model_3d_path" id="field-tmp-model-3d" value="">
<div id="model-3d-progress" class="mt-2 hidden tus-progress-container">
    <div class="flex items-center gap-2">
        <span class="tus-status-icon"></span>
        <span class="tus-progress-text flex-1 text-[10px] text-gray-500"></span>
    </div>
    <div class="mt-1 h-1 w-full overflow-hidden rounded-full bg-gray-200">
        <div class="tus-progress-bar h-full rounded-full bg-penglipuran-green transition-all duration-300" style="width:0%"></div>
    </div>
</div>
```

After USDZ file input (after line 216, before `@error`):
```html
<input type="hidden" name="tmp_model_3d_usdz_path" id="field-tmp-model-3d-usdz" value="">
<div id="model-usdz-progress" class="mt-2 hidden tus-progress-container">
    <!-- same structure -->
</div>
```

Remove `onchange="previewModelGLB(this)"` from `.glb` input and `onchange="previewCategoryUSDZ(this)"` from `.usdz` input.

Add the `@push('styles')` block with tus progress bar styles if not already there.

- [ ] **Step 2: Wire up ChunkedUploader in categories.blade.php**

Add to the `@push('scripts')` section after the existing functions (around line 367):

```js
// Chunked upload initialization
document.addEventListener('DOMContentLoaded', () => {
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
        });
    }

    initChunkedUpload('field-model-3d', 'field-tmp-model-3d', 'model-3d-progress', 20 * 1024 * 1024, ['.glb']);
    initChunkedUpload('field-model-3d-usdz', 'field-tmp-model-3d-usdz', 'model-usdz-progress', 50 * 1024 * 1024, ['.usdz']);
});
```

Also clear tmp hidden inputs in `openCreateModal()` and `openEditModal()`:

In `openCreateModal()` add:
```js
document.getElementById('field-tmp-model-3d').value = '';
document.getElementById('field-tmp-model-3d-usdz').value = '';
```

In `openEditModal()` add:
```js
document.getElementById('field-tmp-model-3d').value = '';
document.getElementById('field-tmp-model-3d-usdz').value = '';
```

- [ ] **Step 3: Update UmkmCategoryController store()**

Replace file handling (lines 50-58):

```php
if ($tmpUuid = $request->input('tmp_model_3d_path')) {
    $validated['model_3d_path'] = \App\Services\TusService::moveFromTemp($tmpUuid, 'models');
} elseif ($request->hasFile('model_3d_file')) {
    $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
}

if ($tmpUuid = $request->input('tmp_model_3d_usdz_path')) {
    $validated['model_3d_usdz_path'] = \App\Services\TusService::moveFromTemp(
        $tmpUuid, 'models', Str::random(40).'.usdz'
    );
} elseif ($request->hasFile('model_3d_usdz_file')) {
    $file = $request->file('model_3d_usdz_file');
    $filename = Str::random(40).'.usdz';
    $validated['model_3d_usdz_path'] = $file->storeAs('models', $filename, 'public');
}
```

Add `use App\Services\TusService;` at top.

- [ ] **Step 4: Update UmkmCategoryController update()**

Replace file handling (lines 95-109):

```php
if ($tmpUuid = $request->input('tmp_model_3d_path')) {
    if ($category->model_3d_path) {
        Storage::disk('public')->delete($category->model_3d_path);
    }
    $validated['model_3d_path'] = TusService::moveFromTemp($tmpUuid, 'models');
} elseif ($request->hasFile('model_3d_file')) {
    if ($category->model_3d_path) {
        Storage::disk('public')->delete($category->model_3d_path);
    }
    $validated['model_3d_path'] = $request->file('model_3d_file')->store('models', 'public');
}

if ($tmpUuid = $request->input('tmp_model_3d_usdz_path')) {
    if ($category->model_3d_usdz_path) {
        Storage::disk('public')->delete($category->model_3d_usdz_path);
    }
    $validated['model_3d_usdz_path'] = TusService::moveFromTemp($tmpUuid, 'models', Str::random(40).'.usdz');
} elseif ($request->hasFile('model_3d_usdz_file')) {
    if ($category->model_3d_usdz_path) {
        Storage::disk('public')->delete($category->model_3d_usdz_path);
    }
    $file = $request->file('model_3d_usdz_file');
    $filename = Str::random(40).'.usdz';
    $validated['model_3d_usdz_path'] = $file->storeAs('models', $filename, 'public');
}
```

---

### Task 5: Test End-to-End

- [ ] **Step 1: Run composer autoload dump**

```bash
cd /home/andndre/Code/ganesha_smart_edutourism
composer dump-autoload
```

- [ ] **Step 2: Create temp directory**

```bash
mkdir -p storage/app/tus/temp storage/app/tus/cache
```

- [ ] **Step 3: Run test**

```bash
php artisan test --filter=AdminTest
```

- [ ] **Step 4: Manual verification checklist**
    1. Open admin → AR Manager → Tambah Model
    2. Select a GLB file → progress bar shows 0-100%
    3. Complete → green check + hidden UUID set
    4. Submit form → model appears in grid with correct GLB
    5. Edit → change GLB → old file replaced
    6. Open admin → UMKM → Categories → same flow for GLB + USDZ
    7. Verify temp UUID not set = fallback to direct upload works
