<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventPublicTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test public events page is rendered successfully.
     */
    public function test_public_events_page_renders_successfully(): void
    {
        // Arrange
        Event::create([
            'name' => 'Ngusaba Kadasa Budaya',
            'slug' => 'ngusaba-kadasa-budaya',
            'category' => 'ceremony',
            'start_datetime' => now()->addDays(2),
            'end_datetime' => now()->addDays(2)->addHours(4),
            'is_free' => true,
            'location_name' => 'Pura Penataran',
        ]);

        Event::create([
            'name' => 'Festival Desa Penglipuran',
            'slug' => 'festival-desa-penglipuran',
            'category' => 'cultural',
            'start_datetime' => now()->addDays(5),
            'end_datetime' => now()->addDays(5)->addHours(8),
            'is_free' => false,
            'price' => 150000,
            'location_name' => 'Jalan Utama Desa',
        ]);

        // Act
        $response = $this->get(route('events'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Ngusaba Kadasa Budaya');
        $response->assertSee('Festival Desa Penglipuran');
        $response->assertSee('Agenda Budaya Penglipuran');
    }

    /**
     * Test public events page is filterable by category query param.
     */
    public function test_public_events_page_filters_by_category(): void
    {
        // Arrange
        Event::create([
            'name' => 'Ritus Adat Sakral',
            'slug' => 'ritus-adat-sakral',
            'category' => 'ceremony',
            'start_datetime' => now()->addDays(2),
            'end_datetime' => now()->addDays(2)->addHours(4),
            'is_free' => true,
            'location_name' => 'Pura Penataran',
        ]);

        Event::create([
            'name' => 'Pertunjukan Seni Tari',
            'slug' => 'pertunjukan-seni-tari',
            'category' => 'cultural',
            'start_datetime' => now()->addDays(5),
            'end_datetime' => now()->addDays(5)->addHours(8),
            'is_free' => true,
            'location_name' => 'Jalan Utama Desa',
        ]);

        // Act with ceremony filter
        $responseCeremony = $this->get(route('events', ['category' => 'Upacara Adat']));
        $responseCeremony->assertStatus(200);
        $responseCeremony->assertSee('Ritus Adat Sakral');
        $responseCeremony->assertDontSee('Pertunjukan Seni Tari');

        // Act with cultural filter
        $responseCultural = $this->get(route('events', ['category' => 'Festival']));
        $responseCultural->assertStatus(200);
        $responseCultural->assertSee('Pertunjukan Seni Tari');
        $responseCultural->assertDontSee('Ritus Adat Sakral');
    }
}
