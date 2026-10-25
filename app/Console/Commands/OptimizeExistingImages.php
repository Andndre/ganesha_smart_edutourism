<?php

namespace App\Console\Commands;

use App\Models\CulturalObject;
use App\Models\Feedback;
use App\Models\UmkmProduct;
use App\Models\UmkmProductCategory;
use App\Models\User;
use App\Services\ImageOptimizerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class OptimizeExistingImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize-existing
                            {--dry-run : Simulate optimization without modifying files or database}
                            {--no-backup : Do not backup original files before converting}
                            {--force : Run without confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downscale existing images to WebP format, backup originals, and update database references';

    protected array $excludedDirectories = [
        'ar-markers',
        'models',
        'models_usdz',
        'audio',
        'backup_images',
    ];

    public function handle(ImageOptimizerService $optimizer): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $noBackup = (bool) $this->option('no-backup');
        $isForce = (bool) $this->option('force');

        $this->info($isDryRun ? '=== [DRY RUN] Existing Image Optimization Simulation ===' : '=== Starting Existing Image Optimization ===');

        // 1. Scan for eligible images
        $files = $this->collectEligibleImages();
        if (empty($files)) {
            $this->info('No unoptimized JPG/PNG images found in public storage.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d candidate image(s) to convert to WebP.', count($files)));

        if (! $isDryRun && ! $isForce) {
            if (! $this->confirm('Proceed with optimizing images and updating database records?', true)) {
                $this->warn('Operation cancelled by user.');

                return self::FAILURE;
            }
        }

        $backupDir = $noBackup ? null : 'backup_images_'.date('Ymd_His');
        if ($backupDir && ! $isDryRun) {
            $this->info(sprintf('Backup directory: storage/app/public/%s', $backupDir));
        }

        $convertedMap = []; // old_path => new_path
        $totalOriginalBytes = 0;
        $totalNewBytes = 0;

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $originalSize = Storage::disk('public')->size($file);
            $totalOriginalBytes += $originalSize;

            $dirname = pathinfo($file, PATHINFO_DIRNAME);
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $newPath = ($dirname === '.' ? '' : $dirname.'/').$filename.'.webp';

            if ($isDryRun) {
                // In dry run, estimate ~75% reduction
                $convertedMap[$file] = $newPath;
                $totalNewBytes += (int) ($originalSize * 0.25);
            } else {
                $optimizedPath = $optimizer->optimizeExistingFile(
                    relativePath: $file,
                    maxWidth: ImageOptimizerService::DEFAULT_MAX_WIDTH,
                    maxHeight: ImageOptimizerService::DEFAULT_MAX_HEIGHT,
                    quality: ImageOptimizerService::DEFAULT_QUALITY,
                    disk: 'public',
                    backupDirectory: $backupDir,
                    deleteOld: true,
                );

                if ($optimizedPath) {
                    $convertedMap[$file] = $optimizedPath;
                    $totalNewBytes += Storage::disk('public')->size($optimizedPath);
                } else {
                    $this->error("\nFailed to optimize: {$file}");
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // 2. Synchronize Database Records
        $updatedRecordsCount = $this->updateDatabaseReferences($convertedMap, $isDryRun);

        // 3. Clear Caches if not dry run
        if (! $isDryRun) {
            try {
                Cache::tags(['cultural_objects', 'umkm', 'explore', 'map'])->flush();
                $this->info('Flushed application content cache tags.');
            } catch (\Throwable) {
                // If cache store does not support tags, ignore
            }
        }

        // 4. Output Summary Report
        $savedBytes = max(0, $totalOriginalBytes - $totalNewBytes);
        $percentSaved = $totalOriginalBytes > 0 ? round(($savedBytes / $totalOriginalBytes) * 100, 1) : 0;

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Images Processed', count($convertedMap)],
                ['Database Records Updated', $updatedRecordsCount],
                ['Original Storage Size', $this->formatBytes($totalOriginalBytes)],
                [$isDryRun ? 'Estimated New Size' : 'New Storage Size', $this->formatBytes($totalNewBytes)],
                ['Saved Space', $this->formatBytes($savedBytes)." ({$percentSaved}%)"],
                ['Backup Created', $backupDir ? "storage/app/public/{$backupDir}" : 'None'],
                ['Execution Mode', $isDryRun ? 'DRY RUN (Simulated)' : 'APPLIED'],
            ]
        );

        $this->info('Image optimization process completed successfully.');

        return self::SUCCESS;
    }

    /**
     * Recursively collect all JPG and PNG files on the public disk, skipping excluded directories.
     */
    protected function collectEligibleImages(): array
    {
        $allFiles = Storage::disk('public')->allFiles();
        $eligible = [];

        foreach ($allFiles as $file) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (! in_array($extension, ['jpg', 'jpeg', 'png'])) {
                continue;
            }

            foreach ($this->excludedDirectories as $excluded) {
                if (str_starts_with($file, $excluded.'/') || str_contains($file, '/'.$excluded.'/')) {
                    continue 2;
                }
            }

            $eligible[] = $file;
        }

        return $eligible;
    }

    /**
     * Update database records with the new converted webp paths.
     */
    protected function updateDatabaseReferences(array $convertedMap, bool $isDryRun): int
    {
        if (empty($convertedMap)) {
            return 0;
        }

        $totalUpdated = 0;

        // A. CulturalObject - historical_images & description
        $culturalObjects = CulturalObject::all();
        foreach ($culturalObjects as $object) {
            $changed = false;

            // 1. historical_images array
            if (is_array($object->historical_images)) {
                $newImages = [];
                foreach ($object->historical_images as $img) {
                    if (isset($convertedMap[$img])) {
                        $newImages[] = $convertedMap[$img];
                        $changed = true;
                    } else {
                        $newImages[] = $img;
                    }
                }
                if ($changed) {
                    $object->historical_images = $newImages;
                }
            }

            // 2. HTML description (TipTap embedded editor-images)
            $descriptions = $object->getTranslations('description');
            if (is_array($descriptions)) {
                foreach ($descriptions as $loc => $html) {
                    if (! is_string($html)) {
                        continue;
                    }
                    $originalHtml = $html;
                    foreach ($convertedMap as $oldPath => $newPath) {
                        if (str_contains($html, $oldPath)) {
                            $html = str_replace($oldPath, $newPath, $html);
                        }
                    }
                    if ($html !== $originalHtml) {
                        $object->setTranslation('description', $loc, $html);
                        $changed = true;
                    }
                }
            }

            if ($changed) {
                $totalUpdated++;
                if (! $isDryRun) {
                    $object->saveQuietly();
                }
                $this->line(" - Updated CulturalObject #{$object->id} ({$object->name})");
            }
        }

        // B. UmkmProduct - images array
        $umkmProducts = UmkmProduct::all();
        foreach ($umkmProducts as $product) {
            if (is_array($product->images)) {
                $newImages = [];
                $changed = false;
                foreach ($product->images as $img) {
                    if (isset($convertedMap[$img])) {
                        $newImages[] = $convertedMap[$img];
                        $changed = true;
                    } else {
                        $newImages[] = $img;
                    }
                }
                if ($changed) {
                    $totalUpdated++;
                    if (! $isDryRun) {
                        $product->images = $newImages;
                        $product->saveQuietly();
                    }
                    $this->line(" - Updated UmkmProduct #{$product->id} ({$product->name})");
                }
            }
        }

        // C. UmkmProductCategory - image_path
        $categories = UmkmProductCategory::all();
        foreach ($categories as $cat) {
            if ($cat->image_path && isset($convertedMap[$cat->image_path])) {
                $totalUpdated++;
                if (! $isDryRun) {
                    $cat->image_path = $convertedMap[$cat->image_path];
                    $cat->saveQuietly();
                }
                $this->line(" - Updated UmkmProductCategory #{$cat->id} ({$cat->name})");
            }
        }

        // D. User - avatar_path
        $users = User::whereNotNull('avatar_path')->get();
        foreach ($users as $user) {
            if (isset($convertedMap[$user->avatar_path])) {
                $totalUpdated++;
                if (! $isDryRun) {
                    $user->avatar_path = $convertedMap[$user->avatar_path];
                    $user->saveQuietly();
                }
                $this->line(" - Updated User #{$user->id} avatar ({$user->name})");
            }
        }

        // E. Feedback - photos array
        $feedbacks = Feedback::whereNotNull('photos')->get();
        foreach ($feedbacks as $fb) {
            if (is_array($fb->photos)) {
                $newPhotos = [];
                $changed = false;
                foreach ($fb->photos as $ph) {
                    if (isset($convertedMap[$ph])) {
                        $newPhotos[] = $convertedMap[$ph];
                        $changed = true;
                    } else {
                        $newPhotos[] = $ph;
                    }
                }
                if ($changed) {
                    $totalUpdated++;
                    if (! $isDryRun) {
                        $fb->photos = $newPhotos;
                        $fb->saveQuietly();
                    }
                    $this->line(" - Updated Feedback #{$fb->id} photos");
                }
            }
        }

        return $totalUpdated;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
