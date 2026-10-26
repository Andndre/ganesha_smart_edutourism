<?php

use App\Models\RouteMission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Synchronize RouteMission config image paths with WebP images already converted on disk.
     */
    public function up(): void
    {
        $disk = Storage::disk('public');
        $missions = RouteMission::all();

        foreach ($missions as $mission) {
            $config = $mission->config;
            if (! is_array($config)) {
                continue;
            }

            $changed = false;
            $updatedConfig = $this->healImagesInArray($config, $disk, $changed);

            if ($changed) {
                $mission->config = $updatedConfig;
                $mission->saveQuietly();
            }
        }
    }

    private function healImagesInArray(array $data, $disk, bool &$changed): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->healImagesInArray($value, $disk, $changed);
            } elseif (is_string($value) && (in_array($key, ['image', 'image_after'], true) || preg_match('/\.(jpe?g|png)($|\?)/i', $value))) {
                $path = parse_url($value, PHP_URL_PATH) ?? $value;
                $relative = ltrim(preg_replace('#^/?(storage/)?#', '', $path), '/');
                $ext = strtolower(pathinfo($relative, PATHINFO_EXTENSION));

                if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                    $dir = pathinfo($relative, PATHINFO_DIRNAME);
                    $filename = pathinfo($relative, PATHINFO_FILENAME);
                    $webpRelative = ($dir === '.' ? '' : $dir.'/').$filename.'.webp';

                    if ($disk->exists($webpRelative) || ! $disk->exists($relative)) {
                        $newValue = preg_replace('/\.(jpe?g|png)($|\?)/i', '.webp$2', $value);
                        if ($newValue !== $value) {
                            $data[$key] = $newValue;
                            $changed = true;
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible WebP path sync
    }
};
