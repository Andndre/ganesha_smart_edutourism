<?php

use App\Models\ArModel;
use App\Models\CulturalObject;
use App\Models\Feedback;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Synchronize database paths with WebP images already converted on disk.
     */
    public function up(): void
    {
        $disk = Storage::disk('public');

        // 1. ArModel - thumbnail_path
        $models = ArModel::whereNotNull('thumbnail_path')->get();
        foreach ($models as $model) {
            $path = $model->thumbnail_path;
            if (! $path) {
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $dir = pathinfo($path, PATHINFO_DIRNAME);
                $filename = pathinfo($path, PATHINFO_FILENAME);
                $webpPath = ($dir === '.' ? '' : $dir.'/').$filename.'.webp';

                if ($disk->exists($webpPath) || ! $disk->exists($path)) {
                    $model->thumbnail_path = $webpPath;
                    $model->saveQuietly();
                }
            }
        }

        // 2. UmkmProductCategory - image_path
        $categories = UmkmProductCategory::whereNotNull('image_path')->get();
        foreach ($categories as $cat) {
            $path = $cat->image_path;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $dir = pathinfo($path, PATHINFO_DIRNAME);
                $filename = pathinfo($path, PATHINFO_FILENAME);
                $webpPath = ($dir === '.' ? '' : $dir.'/').$filename.'.webp';

                if ($disk->exists($webpPath) || ! $disk->exists($path)) {
                    $cat->image_path = $webpPath;
                    $cat->saveQuietly();
                }
            }
        }

        // 3. User - avatar_path
        $users = User::whereNotNull('avatar_path')->get();
        foreach ($users as $user) {
            $path = $user->avatar_path;
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                $dir = pathinfo($path, PATHINFO_DIRNAME);
                $filename = pathinfo($path, PATHINFO_FILENAME);
                $webpPath = ($dir === '.' ? '' : $dir.'/').$filename.'.webp';

                if ($disk->exists($webpPath) || ! $disk->exists($path)) {
                    $user->avatar_path = $webpPath;
                    $user->saveQuietly();
                }
            }
        }

        // 4. CulturalObject - historical_images
        $culturalObjects = CulturalObject::all();
        foreach ($culturalObjects as $co) {
            if (is_array($co->historical_images)) {
                $newImages = [];
                $changed = false;
                foreach ($co->historical_images as $img) {
                    $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                        $dir = pathinfo($img, PATHINFO_DIRNAME);
                        $filename = pathinfo($img, PATHINFO_FILENAME);
                        $webpPath = ($dir === '.' ? '' : $dir.'/').$filename.'.webp';

                        if ($disk->exists($webpPath) || ! $disk->exists($img)) {
                            $newImages[] = $webpPath;
                            $changed = true;

                            continue;
                        }
                    }
                    $newImages[] = $img;
                }
                if ($changed) {
                    $co->historical_images = $newImages;
                    $co->saveQuietly();
                }
            }
        }

        // 5. UmkmProduct - images
        $products = UmkmProduct::all();
        foreach ($products as $p) {
            if (is_array($p->images)) {
                $newImages = [];
                $changed = false;
                foreach ($p->images as $img) {
                    $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                        $dir = pathinfo($img, PATHINFO_DIRNAME);
                        $filename = pathinfo($img, PATHINFO_FILENAME);
                        $webpPath = ($dir === '.' ? '' : $dir.'/').$filename.'.webp';

                        if ($disk->exists($webpPath) || ! $disk->exists($img)) {
                            $newImages[] = $webpPath;
                            $changed = true;

                            continue;
                        }
                    }
                    $newImages[] = $img;
                }
                if ($changed) {
                    $p->images = $newImages;
                    $p->saveQuietly();
                }
            }
        }

        // 6. Feedback - photos
        $feedbacks = Feedback::whereNotNull('photos')->get();
        foreach ($feedbacks as $fb) {
            if (is_array($fb->photos)) {
                $newPhotos = [];
                $changed = false;
                foreach ($fb->photos as $ph) {
                    $ext = strtolower(pathinfo($ph, PATHINFO_EXTENSION));
                    if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                        $dir = pathinfo($ph, PATHINFO_DIRNAME);
                        $filename = pathinfo($ph, PATHINFO_FILENAME);
                        $webpPath = ($dir === '.' ? '' : $dir.'/').$filename.'.webp';

                        if ($disk->exists($webpPath) || ! $disk->exists($ph)) {
                            $newPhotos[] = $webpPath;
                            $changed = true;

                            continue;
                        }
                    }
                    $newPhotos[] = $ph;
                }
                if ($changed) {
                    $fb->photos = $newPhotos;
                    $fb->saveQuietly();
                }
            }
        }

        try {
            Cache::tags(['cultural_objects', 'umkm', 'explore', 'map'])->flush();
        } catch (Throwable) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way sync to WebP
    }
};
