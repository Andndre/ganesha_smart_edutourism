<?php

namespace Tests\Feature;

use App\Models\CapacityZone;
use App\Models\WeatherReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeatherAndDensityTranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_condition_text_translates_for_id_and_en(): void
    {
        $report = new WeatherReport([
            'weather_code' => 2,
            'temperature' => 22.0,
            'condition' => 'Cerah Berawan',
        ]);

        app()->setLocale('id');
        $this->assertEquals('Cerah Berawan', $report->getConditionText());

        app()->setLocale('en');
        $this->assertEquals('Partly Cloudy', $report->getConditionText());
    }

    public function test_weather_condition_translates_from_weather_code_even_if_db_stored_legacy_english_string(): void
    {
        $report = new WeatherReport([
            'weather_code' => 2,
            'temperature' => 22.0,
            'condition' => 'Partly Cloudy', // legacy DB value
        ]);

        app()->setLocale('id');
        $this->assertEquals('Cerah Berawan', $report->getConditionText());

        app()->setLocale('en');
        $this->assertEquals('Partly Cloudy', $report->getConditionText());
    }

    public function test_home_page_displays_weather_and_density_in_indonesian(): void
    {
        WeatherReport::create([
            'id' => 1,
            'temperature' => 22.0,
            'condition' => 'Cerah Berawan',
            'weather_code' => 2,
            'humidity' => 85,
            'wind_speed' => 5.0,
        ]);

        CapacityZone::create([
            'name' => 'Desa Penglipuran',
            'zone_identifier' => 'desa_penglipuran',
            'max_capacity' => 1000,
            'warning_threshold' => 60,
            'critical_threshold' => 80,
            'polygon_coordinates' => [],
            'is_active' => true,
        ]);

        $response = $this->get('/?locale=id');
        $response->assertStatus(200);

        // Weather widget and modal
        $response->assertSee('Cuaca');
        $response->assertSee('Cerah Berawan');
        $response->assertDontSee('Partly Cloudy');

        // Density widget and modal
        $response->assertSee('Kepadatan');
        $response->assertSee('Aman');
    }

    public function test_home_page_displays_weather_and_density_in_english(): void
    {
        WeatherReport::create([
            'id' => 1,
            'temperature' => 22.0,
            'condition' => 'Cerah Berawan',
            'weather_code' => 2,
            'humidity' => 85,
            'wind_speed' => 5.0,
        ]);

        CapacityZone::create([
            'name' => 'Desa Penglipuran',
            'zone_identifier' => 'desa_penglipuran',
            'max_capacity' => 1000,
            'warning_threshold' => 60,
            'critical_threshold' => 80,
            'polygon_coordinates' => [],
            'is_active' => true,
        ]);

        $response = $this->get('/?locale=en');
        $response->assertStatus(200);

        // Weather widget and modal
        $response->assertSee('Weather');
        $response->assertSee('Partly Cloudy');
        $response->assertDontSee('Cerah Berawan');

        // Density widget and modal
        $response->assertSee('Density');
        $response->assertSee('Safe');
    }
}
