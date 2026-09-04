<?php

namespace Tests\Feature;

use App\Models\TicketRate;
use App\Models\TicketScan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketScanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tarif yang diseed migration hanya punya golongan dewasa yang aktif.
     * Tes memakai tarif sendiri supaya angkanya eksplisit di tempat dibaca.
     */
    private function rate(string $origin, string $name, int $price, int $fee = 0): TicketRate
    {
        return TicketRate::create([
            'origin' => $origin,
            'name' => $name,
            'price' => $price,
            'service_fee' => $fee,
            'is_active' => true,
        ]);
    }

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
            'total_price' => 200000,
            'scanned_at' => now(),
            'scanned_by' => $officer->id,
        ]);

        $this->assertDatabaseHas('ticket_scans', [
            'raw_code' => 'TVLK-123',
            'party_size' => 4,
            'total_price' => 200000,
            'duplicate_attempts' => 0,
        ]);
        $this->assertSame($officer->id, $scan->scanner->id);
    }

    public function test_check_reports_new_code_as_new(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $response = $this->actingAs($officer)
            ->postJson('/staff/ticketing/check', ['raw_code' => 'TVLK-BARU-1']);

        $response->assertOk()->assertJson(['status' => 'new']);
    }

    public function test_store_records_a_mixed_party_with_its_price(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        // Angka dari struk MKP: WNA dewasa 50.000, WNI dewasa 25.000, layanan 1.500/orang.
        $foreign = $this->rate('foreign', 'Tes Dewasa', 50000, 1500);
        $domestic = $this->rate('domestic', 'Tes Dewasa', 25000, 1500);

        $response = $this->actingAs($officer)->postJson('/staff/ticketing/store', [
            'raw_code' => 'TVLK-BARU-2',
            'lines' => [
                ['rate_id' => $foreign->id, 'quantity' => 3],
                ['rate_id' => $domestic->id, 'quantity' => 1],
            ],
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('ticket_scans', [
            'code_hash' => TicketScan::hashCode('TVLK-BARU-2'),
            'party_size' => 4,
            // 150.000 + 25.000 retribusi + 4 × 1.500 layanan = 181.000, sama
            // dengan baris Total di struknya.
            'total_price' => 181000,
            'scanned_by' => $officer->id,
        ]);
        $this->assertDatabaseHas('ticket_scan_lines', [
            'origin' => 'foreign',
            'label' => 'Tes Dewasa',
            'quantity' => 3,
            'subtotal' => 150000,
        ]);
    }

    public function test_store_ignores_prices_sent_by_the_browser(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        $rate = $this->rate('domestic', 'Tes Dewasa', 25000, 1500);

        $this->actingAs($officer)->postJson('/staff/ticketing/store', [
            'raw_code' => 'TVLK-MURAH',
            'lines' => [
                ['rate_id' => $rate->id, 'quantity' => 2, 'unit_price' => 1, 'subtotal' => 2],
            ],
        ])->assertOk();

        $this->assertSame(53000, TicketScan::first()->total_price);
    }

    public function test_second_scan_of_same_code_is_rejected_and_counted(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        TicketScan::factory()->create([
            'code_hash' => TicketScan::hashCode('TVLK-DOBEL'),
            'raw_code' => 'TVLK-DOBEL',
            'party_size' => 2,
            'scanned_by' => $officer->id,
        ]);

        $check = $this->actingAs($officer)
            ->postJson('/staff/ticketing/check', ['raw_code' => 'TVLK-DOBEL']);

        $check->assertOk()
            ->assertJson(['status' => 'duplicate'])
            ->assertJsonPath('scan.party_size', 2);

        $this->assertSame(1, TicketScan::count());
        $this->assertSame(1, TicketScan::first()->duplicate_attempts);
        $this->assertNotNull(TicketScan::first()->last_attempt_at);
    }

    public function test_store_refuses_to_create_a_second_row_for_the_same_code(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        TicketScan::factory()->create([
            'code_hash' => TicketScan::hashCode('TVLK-RACE'),
            'raw_code' => 'TVLK-RACE',
            'scanned_by' => $officer->id,
        ]);

        $response = $this->actingAs($officer)->postJson('/staff/ticketing/store', [
            'raw_code' => 'TVLK-RACE',
            'lines' => [['rate_id' => $this->rate('domestic', 'Tes Dewasa', 25000)->id, 'quantity' => 5]],
        ]);

        $response->assertStatus(409)->assertJson(['success' => false, 'status' => 'duplicate']);
        $this->assertSame(1, TicketScan::count());
        $this->assertSame(1, TicketScan::first()->duplicate_attempts);
        $this->assertNotNull(TicketScan::first()->last_attempt_at);
    }

    public function test_visitor_name_is_optional_and_falls_back_to_pengunjung(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $this->actingAs($officer)->postJson('/staff/ticketing/store', [
            'raw_code' => 'TVLK-ANON',
            'lines' => [['rate_id' => $this->rate('domestic', 'Tes Dewasa', 25000)->id, 'quantity' => 1]],
        ])->assertOk()->assertJsonPath('scan.visitor_name', 'Pengunjung');

        $this->assertNull(TicketScan::first()->visitor_name);
    }

    public function test_store_rejects_an_empty_or_unknown_breakdown(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $this->actingAs($officer)->postJson('/staff/ticketing/store', [
            'raw_code' => 'TVLK-KOSONG',
            'lines' => [],
        ])->assertStatus(422)->assertJsonValidationErrors(['lines']);

        $this->actingAs($officer)->postJson('/staff/ticketing/store', [
            'raw_code' => 'TVLK-HANTU',
            'lines' => [['rate_id' => 999999, 'quantity' => 0]],
        ])->assertStatus(422)->assertJsonValidationErrors(['lines.0.rate_id', 'lines.0.quantity']);

        $this->assertSame(0, TicketScan::count());
    }

    public function test_store_rejects_a_party_larger_than_the_cap_across_all_categories(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        $adult = $this->rate('domestic', 'Tes Dewasa', 25000);
        $child = $this->rate('domestic', 'Tes Anak', 15000);

        $this->actingAs($officer)->postJson('/staff/ticketing/store', [
            'raw_code' => 'TVLK-ROMBONGAN',
            'lines' => [
                ['rate_id' => $adult->id, 'quantity' => 150],
                ['rate_id' => $child->id, 'quantity' => 150],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors(['lines']);

        $this->assertSame(0, TicketScan::count());
    }

    public function test_tourists_cannot_reach_scan_endpoints(): void
    {
        $tourist = User::factory()->create(['role' => 'tourist']);

        $this->actingAs($tourist)->get('/staff/ticketing')->assertForbidden();
        $this->actingAs($tourist)
            ->postJson('/staff/ticketing/check', ['raw_code' => 'X'])
            ->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/staff/ticketing')->assertRedirect('/login');
    }

    public function test_stats_counts_people_not_rows(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        TicketScan::factory()->withLine('domestic', 4)
            ->create(['party_size' => 4, 'scanned_at' => now(), 'scanned_by' => $officer->id]);
        TicketScan::factory()->withLine('foreign', 3)
            ->create(['party_size' => 3, 'scanned_at' => now(), 'scanned_by' => $officer->id]);

        $response = $this->actingAs($officer)->get('/staff/ticketing/history');

        $response->assertOk()
            ->assertViewHas('totalVisitors', 7)
            ->assertViewHas('totalTickets', 2)
            ->assertViewHas('domesticVisitors', 4)
            ->assertViewHas('foreignVisitors', 3);
    }

    public function test_stats_splits_a_mixed_ticket_across_both_origins(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        TicketScan::factory()
            ->withLine('domestic', 1, 'Dewasa', 25000)
            ->withLine('foreign', 3, 'Dewasa', 50000)
            ->create(['party_size' => 4, 'total_price' => 181000, 'scanned_at' => now(), 'scanned_by' => $officer->id]);

        $this->actingAs($officer)->get('/staff/ticketing/history')
            ->assertOk()
            ->assertViewHas('totalTickets', 1)
            ->assertViewHas('totalVisitors', 4)
            ->assertViewHas('domesticVisitors', 1)
            ->assertViewHas('foreignVisitors', 3)
            ->assertViewHas('totalRevenue', 181000);
    }

    public function test_stats_excludes_scans_outside_the_selected_day(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        TicketScan::factory()->create(['party_size' => 5, 'scanned_at' => now()->subDays(3), 'scanned_by' => $officer->id]);

        $this->actingAs($officer)->get('/staff/ticketing/history')
            ->assertOk()
            ->assertViewHas('totalVisitors', 0);
    }

    public function test_stats_custom_preset_filters_by_the_given_date_range(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        TicketScan::factory()->create(['party_size' => 4, 'scanned_at' => now()->subDays(2), 'scanned_by' => $officer->id]);
        TicketScan::factory()->create(['party_size' => 3, 'scanned_at' => now()->subDays(20), 'scanned_by' => $officer->id]);

        $response = $this->actingAs($officer)->get('/staff/ticketing/history?'.http_build_query([
            'preset' => 'custom',
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertOk()
            ->assertViewHas('preset', 'custom')
            ->assertViewHas('totalVisitors', 4)
            ->assertViewHas('totalTickets', 1);
    }

    public function test_stats_rejects_an_unparseable_start_date_instead_of_500ing(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $response = $this->actingAs($officer)
            ->get('/staff/ticketing/history?preset=custom&start_date=abc');

        // This is a plain <form method="GET"> page, so an invalid query string
        // redirects back with flashed errors instead of a raw 422 — either way,
        // it must not throw and 500 the page.
        $response->assertStatus(302)->assertSessionHasErrors(['start_date']);
    }

    public function test_stats_rejects_a_start_date_after_the_end_date(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $response = $this->actingAs($officer)->get('/staff/ticketing/history?'.http_build_query([
            'preset' => 'custom',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->subDays(3)->format('Y-m-d'),
        ]));

        $response->assertStatus(302)->assertSessionHasErrors(['end_date']);
    }

    public function test_stats_rejects_an_end_date_before_the_defaulted_start_date(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        // Only end_date supplied: start_date defaults to seven days ago, which
        // lands after the given end date. The after_or_equal rule cannot see
        // that, so the resolved range has to be checked as well.
        $response = $this->actingAs($officer)->get('/staff/ticketing/history?'.http_build_query([
            'preset' => 'custom',
            'end_date' => now()->subDays(30)->format('Y-m-d'),
        ]));

        $response->assertStatus(302)->assertSessionHasErrors(['end_date']);
    }

    public function test_admin_dashboard_shows_scanned_visitor_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        TicketScan::factory()->create(['party_size' => 6, 'scanned_at' => now()]);

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertViewHas('todayScannedVisitors', 6)
            ->assertViewHas('todayTicketCount', 1);
    }
}
