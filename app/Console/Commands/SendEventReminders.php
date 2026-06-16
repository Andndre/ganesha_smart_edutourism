<?php

namespace App\Console\Commands;

use App\Events\EventReminderSent;
use App\Models\Event;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Broadcast reminders for cultural events starting in ~15 minutes';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $upcomingEvents = Event::whereBetween('start_datetime', [
            now()->addMinutes(14),
            now()->addMinutes(16),
        ])->get();

        foreach ($upcomingEvents as $event) {
            broadcast(new EventReminderSent(
                $event->name,
                $event->location_name ?? 'Desa Penglipuran',
                $event->start_datetime->format('H:i'),
                $event->category ?? 'cultural',
            ));

            $this->info("Reminder sent for: {$event->name}");
        }

        if ($upcomingEvents->isEmpty()) {
            $this->info('No upcoming events to remind.');
        }
    }
}
