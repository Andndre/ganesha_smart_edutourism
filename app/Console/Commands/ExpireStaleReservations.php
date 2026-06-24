<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('reservations:expire-stale')]
#[Description('Expire stale reservations past their scheduled date')]
class ExpireStaleReservations extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $expiredCount = Reservation::where('status', 'confirmed')
            ->where('scheduled_date', '<', Carbon::today())
            ->update([
                'status' => 'expired',
                'cancelled_at' => now(),
                'cancellation_type' => 'system_expire',
                'cancellation_note' => 'Expired by system: scheduled date passed',
            ]);

        $cancelledCount = Reservation::where('status', 'pending')
            ->where('created_at', '<', Carbon::now()->subHours(24))
            ->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_type' => 'system_expire',
                'cancellation_note' => 'Cancelled by system: pending > 24 hours',
            ]);

        Log::info("Expired {$expiredCount} confirmed, cancelled {$cancelledCount} pending reservations");

        $this->info("Expired {$expiredCount} confirmed, cancelled {$cancelledCount} pending reservations");
    }
}
