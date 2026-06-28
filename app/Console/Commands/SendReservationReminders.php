<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Notifications\ReservationReminderNotification;
use Illuminate\Console\Command;

class SendReservationReminders extends Command
{
    protected $signature = 'reservations:send-reminders';
    protected $description = 'Send push notification reminders for tomorrow\'s reservations';

    public function handle(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $count = 0;
        Reservation::whereDate('scheduled_date', $tomorrow)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereNotNull('user_id')
            ->with('user')
            ->each(function (Reservation $reservation) use (&$count) {
                $reservation->user->notify(new ReservationReminderNotification($reservation));
                $count++;
            });

        $this->info("Sent {$count} reservation reminders for {$tomorrow}.");
    }
}
