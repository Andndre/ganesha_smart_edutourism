<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VillageDensityUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array{key: string, label: string, color: string, barColor: string}  $status
     */
    public function __construct(
        public int $totalCurrent,
        public int $totalMax,
        public int $densityPercent,
        public array $status,
    ) {}

    public function broadcastAs(): string
    {
        return 'VillageDensityUpdated';
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('village-notifications'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'total_current' => $this->totalCurrent,
            'total_max' => $this->totalMax,
            'density_percent' => $this->densityPercent,
            'status' => $this->status,
        ];
    }
}
