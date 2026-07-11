<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ZoneOccupancyUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array{key: string, label: string, color: string, barColor: string}  $status
     */
    public function __construct(
        public int $zoneId,
        public string $zoneIdentifier,
        public int $currentCount,
        public int $maxCapacity,
        public int $occupancyPercentage,
        public array $status,
    ) {}

    public function broadcastAs(): string
    {
        return 'ZoneOccupancyUpdated';
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('village-map'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'zone_id' => $this->zoneId,
            'zone_identifier' => $this->zoneIdentifier,
            'current_count' => $this->currentCount,
            'max_capacity' => $this->maxCapacity,
            'occupancy_percentage' => $this->occupancyPercentage,
            'status' => $this->status,
        ];
    }
}
