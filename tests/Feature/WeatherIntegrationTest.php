<?php

namespace Tests\Feature;

use App\Models\WeatherReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the app:update-weather command fetches API data and saves in DB cache.
     */
    public function test_weather_command_fetches_api_and_saves_in_db(): void
    {
        // Mock Open-Meteo API response
        Http::fake([
            'https://api.open-meteo.com/v1/forecast*' => Http::response([
                'current' => [
                    'temperature_2m' => 24.5,
                    'relative_humidity_2m' => 85,
                    'weather_code' => 1, // Cerah Berawan
                    'wind_speed_10m' => 5.5,
                ],
            ], 200),
        ]);

        // Run the Artisan command
        $this->artisan('app:update-weather')
            ->expectsOutput('Fetching weather forecast for coordinates: -8.422303596762355, 115.35948833933173...')
            ->expectsOutput('Weather cache updated successfully!')
            ->assertSuccessful();

        // Assert database holds the record
        $this->assertDatabaseHas('weather_reports', [
            'id' => 1,
            'temperature' => 24.5,
            'condition' => 'Cerah Berawan',
            'weather_code' => 1,
            'humidity' => 85,
            'wind_speed' => 5.5,
        ]);
    }

    /**
     * Test home page renders dynamic weather from database cache.
     */
    public function test_home_renders_dynamic_weather_data(): void
    {
        // Pre-populate DB weather report cache
        WeatherReport::create([
            'id' => 1,
            'temperature' => 26.3,
            'condition' => 'Berkabut',
            'weather_code' => 45,
            'humidity' => 95,
            'wind_speed' => 2.0,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Check if dynamic temperature and mapped text is in home page HTML
        $response->assertSee('26°C');
        $response->assertSee('Berkabut');
    }

    /**
     * Test home page automatically triggers weather update command on first-time access when cache is empty.
     */
    public function test_home_triggers_weather_update_automatically_when_db_is_empty(): void
    {
        // Verify database starts with no weather reports
        $this->assertDatabaseCount('weather_reports', 0);

        // Mock the API response when HomeController executes Artisan call
        Http::fake([
            'https://api.open-meteo.com/v1/forecast*' => Http::response([
                'current' => [
                    'temperature_2m' => 21.0,
                    'relative_humidity_2m' => 80,
                    'weather_code' => 3, // Berawan
                    'wind_speed_10m' => 8.2,
                ],
            ], 200),
        ]);

        // Access the home page
        $response = $this->get(route('home'));

        $response->assertStatus(200);

        // Verify the database has the automatically cached report
        $this->assertDatabaseHas('weather_reports', [
            'id' => 1,
            'temperature' => 21.0,
            'condition' => 'Berawan',
            'weather_code' => 3,
        ]);

        // Verify it was rendered on the page
        $response->assertSee('21°C');
        $response->assertSee('Berawan');
    }

    /**
     * Test weather report returns correct icons for day and night.
     */
    public function test_weather_report_returns_correct_icons_for_day_and_night(): void
    {
        $timezone = config('services.penglipuran.timezone', 'Asia/Makassar');

        $report = new WeatherReport([
            'weather_code' => 0,
            'temperature' => 30.0,
            'condition' => 'Cerah',
        ]);

        // 1. Day time: 12:00 PM WITA
        Carbon::setTestNow(Carbon::create(2026, 6, 11, 12, 0, 0, $timezone));
        $dayIcon = $report->getIconHtml();
        $this->assertStringContainsString('text-amber-500', $dayIcon);
        $this->assertStringContainsString('circle cx="12"', $dayIcon);

        // 2. Night time: 8:00 PM WITA
        Carbon::setTestNow(Carbon::create(2026, 6, 11, 20, 0, 0, $timezone));
        $nightIcon = $report->getIconHtml();
        $this->assertStringContainsString('text-indigo-300', $nightIcon);
        $this->assertStringContainsString('path d="M12 3a6', $nightIcon);

        $cloudReport = new WeatherReport([
            'weather_code' => 1,
            'temperature' => 28.0,
            'condition' => 'Cerah Berawan',
        ]);

        // 3. Day time: 12:00 PM WITA
        Carbon::setTestNow(Carbon::create(2026, 6, 11, 12, 0, 0, $timezone));
        $dayCloudIcon = $cloudReport->getIconHtml();
        $this->assertStringContainsString('text-amber-500', $dayCloudIcon);
        $this->assertStringContainsString('text-blue-400', $dayCloudIcon);

        // 4. Night time: 8:00 PM WITA
        Carbon::setTestNow(Carbon::create(2026, 6, 11, 20, 0, 0, $timezone));
        $nightCloudIcon = $cloudReport->getIconHtml();
        $this->assertStringContainsString('text-indigo-300', $nightCloudIcon);
        $this->assertStringContainsString('text-blue-400', $nightCloudIcon);

        // Clean up test time
        Carbon::setTestNow();
    }
}
