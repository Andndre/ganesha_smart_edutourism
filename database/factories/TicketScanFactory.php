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
            'total_price' => 0,
            'scanned_at' => now(),
            'scanned_by' => User::factory()->state(['role' => 'ticket_officer']),
            'duplicate_attempts' => 0,
            'last_attempt_at' => null,
        ];
    }

    /**
     * Tambah satu golongan ke tiket, mis. ->withLine('foreign', 3, 'Dewasa').
     *
     * Tidak dipasang otomatis: sebagian tes hanya peduli pada party_size, dan
     * membuat baris rincian palsu di situ malah mengaburkan yang diuji.
     */
    public function withLine(string $origin, int $quantity, string $label = 'Dewasa', int $unitPrice = 0): static
    {
        return $this->afterCreating(fn (TicketScan $scan) => $scan->lines()->create([
            'origin' => $origin,
            'label' => $label,
            'unit_price' => $unitPrice,
            'unit_fee' => 0,
            'quantity' => $quantity,
            'subtotal' => $unitPrice * $quantity,
        ]));
    }
}
