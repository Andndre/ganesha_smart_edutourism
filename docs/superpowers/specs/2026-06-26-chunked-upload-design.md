# Chunked File Upload with Progress Bar — Admin GLB/USDZ Upload

**Date:** 2026-06-26  
**Status:** Draft

## Problem

Admin upload GLB (max 20MB) dan USDZ (max 50MB) untuk AR Manager dan UMKM Product Categories. File besar di jaringan tidak stabil bisa gagal di tengah. Tidak ada progress bar — user melihat layar diam tanpa feedback.

## Solution

Chunked upload via **tus-php** + **tus-js-client** dengan progress bar real-time di semua modal form admin.

## Architecture

```
[User pilih file] → [tus-js-client upload chunked ke /api/tus/upload]
   → [tus-php simpan ke storage/app/tus/temp/]
   → [complete → hidden input diisi temp UUID]
→ [User submit form] → [Controller: baca temp UUID, copy file ke final storage]

Room:
- php artisan app:cleanup-tus → hapus file temp >24 jam (schedule daily)
```

## Tus Server Setup

- Package: `ankitpokhrel/tus-php` v3
- Routes: `POST /api/tus/upload` (create), `PATCH /api/tus/upload/{uuid}` (chunk), `HEAD /api/tus/upload/{uuid}` (status)
- Config: `TUS-UPLOAD-EXPIRY: 86400`, max 52428800 bytes (50MB), laravel disk-based temp
- Server handler dipasang di `routes/api.php` (bukan file controller — tus-php pake closure-style handler)

## Temp File Flow

1. Upload selesai → tus server return UUID
2. Client set hidden input value = `{uuid}.{original_extension}`
3. Form submit → controller panggil `TusService::moveFromTemp(uuid, finalPath, finalFilename)`
4. `TusService` check file exists di temp, copy ke public disk, delete dari temp
5. Update DB dengan final path (sama seperti skema existing)

## File Locations

Current storage patterns (not changing):
- AR Manager GLB: `models/` via `$path = $file->store('models', 'public')`
- AR Manager USDZ: `models_usdz/` via manual path (`'models_usdz/'.Str::random(40).'.usdz'`)
- UMKM Category GLB: `models/` via store
- UMKM Category USDZ: `models/` via manual path

In `moveFromTemp()`, reuse existing path logic.

## Frontend

### 1. `resources/js/chunked-upload.js` — Reusable class

```js
class ChunkedUploader {
  constructor(inputElement, options)
  // options: allowedTypes[string], maxSize[number], onProgress[fn(bytesSent, bytesTotal)],
  //           onComplete[fn(uuid, originalName)], onError[fn(error)], hiddenInput[string],
  //           progressSelector[string], submitButtonSelector[string]

  // Internal:
  // - on file change: validate type & size, create tus.Upload
  // - tus.Upload findUploadUrlFromPrevious / fingerprint = file hash
  // - onProgress update progress bar element
  // - onSuccess set hidden input value = UUID.extension
  // - disable submit button while uploading
}
```

### 2. Modal Changes per Form

**AR Manager modal-form.blade.php (3 file inputs):**
- GLB input: progress bar di bawah input, hidden `tmp_model_3d_path`
- USDZ input: progress bar di bawah input, hidden `tmp_model_3d_usdz_path`
- Audio input: progress bar di bawah input, hidden `tmp_audio_narration_path`
- Submit button disabled while ANY upload pending

**UMKM Categories categories.blade.php (2 file inputs):**
- GLB input: progress bar di bawah input, hidden `tmp_model_3d_path`
- USDZ input: progress bar di bawah input, hidden `tmp_model_3d_usdz_path`
- Submit button disabled while ANY upload pending

### 3. UI Pattern per File Input

```
[ Select File ] ─────────────┐
[████████████░░░░░] 65%      │ (hidden until upload starts)
[ Cancel ]                   │
✓ Upload complete            │ (green check + file name shown)
```

Progress bar: TailwindCSS `bg-penglipuran-green` progress bar, height 4px.
Text below bar: percentage + bytes uploaded / total.

### 4. Edit Mode

- Jika user IS editing dan TIDAK ganti file → file existing tetap dipake, no upload needed
- Jika user ganti file → upload baru, temp UUID, setelah form submit controller hapus file lama lalu pake yang baru
- Progress bar muncul pas file baru dipilih

## Server-side Changes

### New Files

| File | Purpose |
|---|---|
| `app/Http/Controllers/Api/TusController.php` | Tus server handler wrapper |
| `app/Services/TusService.php` | `moveFromTemp()`, `cleanup()` |
| `app/Console/Commands/CleanupTusTemp.php` | Hapus temp >24 jam |
| `resources/js/chunked-upload.js` | Client chunked upload class |

### Modified Files

| File | Changes |
|---|---|
| `routes/api.php` | Tambah tus routes |
| `app/Http/Controllers/Admin/ARManagerController.php` | `storeModel()` & `updateModel()` cek temp UUID dulu baru file upload langsung |
| `app/Http/Controllers/Admin/UmkmCategoryController.php` | `store()` & `update()` cek temp UUID |
| `resources/views/admin/ar-manager/partials/modal-form.blade.php` | Ganti file input jadi chunked + progress bar |
| `resources/views/admin/umkm/categories.blade.php` | Ganti file input jadi chunked + progress bar |
| `routes/console.php` | Schedule `app:cleanup-tus` |

### Controller Logic Change

**Before (current ARManagerController::storeModel):**
```php
$model_3d_path = $request->file('model_3d_file')->store('models', 'public');
```

**After:**
```php
if ($request->input('tmp_model_3d_path')) {
    $model_3d_path = TusService::moveFromTemp($request->input('tmp_model_3d_path'), 'models');
} elseif ($request->hasFile('model_3d_file')) {
    $model_3d_path = $request->file('model_3d_file')->store('models', 'public');
}
```

(Keep fallback to direct upload for non-chunked/fallback case.)

## Error Handling

- Tus server: file size >50MB ditolak, tipe file diperiksa client-side + server
- Upload gagal di tengah: retry otomatis via tus-js-client (3 attempts), user lihat progress restart
- Upload gagal permanen: progress bar merah, error message, user bisa retry
- Submit form tanpa upload selesai: validation error "file is still uploading" — tapi ideally button udah disabled

## Security

- Tus route di middleware `auth` + `admin` (hanya admin bisa upload)
- Temp file tidak bisa diakses publik (di `storage/app/tus/`, bukan `storage/app/public/`)
- Extension divalidasi setelah upload sebelum copy ke public

## Performance

- Chunk size: default 5MB (konfigurabel via tus-js-client)
- Upload concurreny: 1 file at a time per form (tidak perlu parallel)
- Large files (50MB USDZ) dipecah jadi 10 chunk, upload partial dulu baru request completion

## Test

- Upload GLB 20MB → progress bar 0-100%, UUID hidden input terisi
- Cancel upload → file temp terhapus (via expiry)
- Submit form dengan temp UUID → file muncul di final storage + DB entry
- Edit form tanpa ganti file → file existing tetap
- Edit form ganti file → temp UUID, file lama terhapus
- Cleanup command: buat file temp lawas, jalankan `app:cleanup-tus`, file terhapus
- Fail scenario: submit form ketika upload belum selesai → validation error
