<?php

namespace Database\Factories;

use App\Models\TicketScan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketScan>
 */
class TicketScanFactory extends Factory
{
    protected $model = TicketScan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rawCode = 'TVLK-'.strtoupper($this->faker->bothify('??####'));

        return [
            'code_hash' => TicketScan::hashCode($rawCode),
            'raw_code' => $rawCode,
            'visitor_name' => null,
            'party_size' => 2,
            'origin' => 'domestic',
            'scanned_at' => now(),
            'scanned_by' => User::factory()->state(['role' => 'ticket_officer']),
            'duplicate_attempts' => 0,
            'last_attempt_at' => null,
        ];
    }
}
