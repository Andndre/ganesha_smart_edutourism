<?php

namespace App\Console\Commands;

use App\Models\UmkmProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncUmkmCovers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-umkm-covers {--force : Overwrite custom uploaded covers as well}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set default cover image for all village UMKM profiles and flush cache';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $defaultImage = 'images/packages/kuliner-paon.webp';
        $force = $this->option('force');

        $query = UmkmProfile::query();
        if (! $force) {
            $query->whereNull('image_path')
                ->orWhere('image_path', '')
                ->orWhere('image_path', 'LIKE', 'images/%');
        }

        $count = $query->update(['image_path' => $defaultImage]);

        Cache::tags(['explore', 'umkm'])->flush();

        $this->info("Updated {$count} UMKM profile(s) with default cover: {$defaultImage}");
        $this->info('Flushed explore & umkm cache tags.');

        return Command::SUCCESS;
    }
}

