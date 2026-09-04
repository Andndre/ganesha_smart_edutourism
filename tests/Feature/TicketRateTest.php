<?php

namespace Tests\Feature;

use App\Models\TicketRate;
use App\Models\TicketScan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_seeds_only_the_rates_printed_on_the_receipt(): void
    {
        $adultDomestic = TicketRate::where('origin', 'domestic')->where('name', 'Dewasa')->sole();
        $adultForeign = TicketRate::where('origin', 'foreign')->where('name', 'Dewasa')->sole();

        $this->assertSame(25000, $adultDomestic->price);
        $this->assertSame(50000, $adultForeign->price);
        $this->assertSame(1500, $adultDomestic->service_fee);

        // Anak dan lansia belum punya harga resmi, jadi harus mati sampai diisi.
        $this->assertSame(0, TicketRate::active()->where('price', 0)->count());
    }

    public function test_admin_can_add_update_and_remove_a_rate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/ticket-rates', [
            'origin' => 'domestic',
            'name' => 'Pelajar',
            'price' => 10000,
            'service_fee' => 1500,
            'sort_order' => 4,
        ])->assertRedirect(route('admin.ticket-rates'));

        $rate = TicketRate::where('name', 'Pelajar')->sole();
        $this->assertTrue($rate->is_active);

        $this->actingAs($admin)->put("/admin/ticket-rates/{$rate->id}", [
            'origin' => 'domestic',
            'name' => 'Pelajar',
            'price' => 12000,
            'service_fee' => 1500,
            'sort_order' => 4,
        ])->assertRedirect(route('admin.ticket-rates'));

        // Checkbox yang tidak dicentang tidak ikut terkirim: itu berarti nonaktif,
        // bukan "biarkan seperti semula".
        $this->assertSame(12000, $rate->fresh()->price);
        $this->assertFalse($rate->fresh()->is_active);

        $this->actingAs($admin)->delete("/admin/ticket-rates/{$rate->id}")
            ->assertRedirect(route('admin.ticket-rates'));
        $this->assertNull(TicketRate::find($rate->id));
    }

    public function test_a_rate_name_cannot_repeat_within_the_same_origin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/ticket-rates', [
            'origin' => 'domestic',
            'name' => 'Dewasa',
            'price' => 30000,
            'service_fee' => 0,
        ])->assertSessionHasErrors('name');

        // Nama yang sama pada asal berbeda tetap boleh.
        $this->actingAs($admin)->post('/admin/ticket-rates', [
            'origin' => 'foreign',
            'name' => 'Pelajar',
            'price' => 30000,
            'service_fee' => 0,
        ])->assertSessionHasNoErrors();
    }

    public function test_deleting_a_rate_keeps_the_visits_already_recorded_on_it(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);
        $rate = TicketRate::where('origin', 'domestic')->where('name', 'Dewasa')->sole();

        $this->actingAs($officer)->postJson('/staff/ticketing/store', [
            'raw_code' => 'TVLK-ARSIP',
            'lines' => [['rate_id' => $rate->id, 'quantity' => 2]],
        ])->assertOk();

        $rate->delete();

        $line = TicketScan::first()->lines()->sole();
        $this->assertNull($line->ticket_rate_id);
        $this->assertSame('Dewasa', $line->label);
        $this->assertSame(50000, $line->subtotal);
    }

    public function test_only_active_rates_reach_the_scan_form(): void
    {
        $officer = User::factory()->create(['role' => 'ticket_officer']);

        $this->actingAs($officer)->get('/staff/ticketing')
            ->assertOk()
            ->assertViewHas('rates', fn ($rates) => $rates->every->is_active && $rates->count() === 2);
    }

    public function test_view_only_admin_cannot_change_rates(): void
    {
        $viewer = User::factory()->create(['role' => 'admin_viewer']);

        $this->actingAs($viewer)->get('/admin/ticket-rates')->assertOk();
        $this->actingAs($viewer)->post('/admin/ticket-rates', [
            'origin' => 'domestic',
            'name' => 'Pelajar',
            'price' => 10000,
            'service_fee' => 0,
        ])->assertForbidden();
    }
}
