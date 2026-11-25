<?php

namespace App\Console\Commands;

use App\Services\UnusedArAssetCleaner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupArAssetsCommand extends Command
{
    protected $signature = 'app:cleanup-ar-assets';

    protected $description = 'Hapus aset AR lama yang tidak lagi digunakan setelah migrasi';

    public function handle(UnusedArAssetCleaner $cleaner): int
    {
        $disk = Storage::disk('local');
        if ($disk->exists(UnusedArAssetCleaner::MANIFEST)) {
            $paths = json_decode($disk->get(UnusedArAssetCleaner::MANIFEST), true, flags: JSON_THROW_ON_ERROR);
            $cleaner->deleteUnused($paths);
            $disk->delete(UnusedArAssetCleaner::MANIFEST);
        }

        $this->info('Pembersihan aset AR selesai.');

        return self::SUCCESS;
    }
}
