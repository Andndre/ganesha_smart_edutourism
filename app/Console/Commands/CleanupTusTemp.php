<?php

namespace App\Console\Commands;

use App\Services\TusService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cleanup-tus')]
#[Description('Delete expired tus temp upload files older than 24 hours')]
class CleanupTusTemp extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = TusService::cleanTemp(24);
        $this->info("Cleaned {$count} expired temp files.");
    }
}
