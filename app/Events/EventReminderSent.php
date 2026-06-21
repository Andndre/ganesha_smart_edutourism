<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventReminderSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $eventName,
        public string $locationName,
        public string $startTime,
        public string $category,
    ) {}

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'EventReminderSent';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('village-notifications'),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'event_name' => $this->eventName,
            'location_name' => $this->locationName,
            'start_time' => $this->startTime,
            'category' => $this->category,
        ];
    }
}
