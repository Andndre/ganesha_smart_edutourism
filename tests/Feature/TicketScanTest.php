<?php

namespace Tests\Feature;

use App\Models\TicketScan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_hash_code_is_stable_and_ignores_surrounding_whitespace(): void
    {
        $this->assertSame(
            TicketScan::hashCode('TVLK-123'),
            TicketScan::hashCode('  TVLK-123  ')
        );
        $this->assertSame(64, strlen(TicketScan::hashCode('TVLK-123')));
    }

    public function test_scan_can_be_persisted_with_its_scanner(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $scan = TicketScan::create([
            'code_hash' => TicketScan::hashCode('TVLK-123'),
            'raw_code' => 'TVLK-123',
            'visitor_name' => null,
            'party_size' => 4,
            'origin' => 'foreign',
            'scanned_at' => now(),
            'scanned_by' => $officer->id,
        ]);

        $this->assertDatabaseHas('ticket_scans', [
            'raw_code' => 'TVLK-123',
            'party_size' => 4,
            'origin' => 'foreign',
            'duplicate_attempts' => 0,
        ]);
        $this->assertSame($officer->id, $scan->scanner->id);
    }
}
