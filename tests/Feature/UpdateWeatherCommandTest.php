<?php

namespace Tests\Feature;

use App\Models\WeatherReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Sleep;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class UpdateWeatherCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_connection_failure_preserves_last_report_and_throttles_retries_and_logs(): void
    {
        $this->freezeTime();
        $report = WeatherReport::create([
            'id' => 1, 'temperature' => 25, 'condition' => 'Berawan',
            'weather_code' => 3, 'humidity' => 80, 'wind_speed' => 2,
        ]);
        $previous = $report->fresh()->getAttributes();
        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::failedConnection('Connection timed out')]);
        Sleep::fake();
        Log::spy();

        $this->artisan('app:update-weather')->assertSuccessful();
        $this->artisan('app:update-weather')->assertSuccessful();
        $this->travel(11)->minutes();
        $this->artisan('app:update-weather')->assertSuccessful();

        Http::assertSentCount(4);
        Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $message, array $context): bool => $context['reason'] === 'connection_failed' && ! isset($context['exception'])
        );
        Log::shouldNotHaveReceived('error');
        $this->assertSame($previous, $report->fresh()->getAttributes());

        $this->travel(50)->minutes();
        $this->artisan('app:update-weather')->assertSuccessful();

        Log::shouldHaveReceived('warning')->twice();
    }

    #[DataProvider('transientFailures')]
    public function test_transient_failure_is_retried_and_recovers(string|int $failure): void
    {
        Http::preventStrayRequests();
        $sequence = Http::fakeSequence('api.open-meteo.com/*');
        if ($failure === 'connection') {
            $sequence->pushFailedConnection();
        } else {
            $sequence->pushStatus($failure);
        }
        $sequence->push($this->validPayload());
        Sleep::fake();
        Log::spy();

        $this->artisan('app:update-weather')->assertSuccessful();

        Http::assertSentCount(2);
        Log::shouldNotHaveReceived('warning');
        $this->assertDatabaseHas('weather_reports', ['id' => 1, 'temperature' => 24.5, 'weather_code' => 1]);
    }

    public static function transientFailures(): array
    {
        return ['connection' => ['connection'], 'rate limit' => [429], 'server error' => [503]];
    }

    #[DataProvider('httpFailures')]
    public function test_http_failure_is_handled_without_scheduler_failure_or_logging_response_body(int $status, int $attempts): void
    {
        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response('Large upstream error page', $status)]);
        Sleep::fake();
        Log::spy();

        $this->artisan('app:update-weather')->assertSuccessful();

        Http::assertSentCount($attempts);
        Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $message, array $context): bool => $context === ['reason' => 'http_error', 'status' => $status]
        );
        Log::shouldNotHaveReceived('error');
        $this->assertDatabaseCount('weather_reports', 0);
    }

    public static function httpFailures(): array
    {
        return ['client error' => [400, 1], 'rate limit' => [429, 2], 'server error' => [503, 2]];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_response_does_not_overwrite_last_report(array|string $payload): void
    {
        $report = WeatherReport::create([
            'id' => 1, 'temperature' => 25, 'condition' => 'Berawan',
            'weather_code' => 3, 'humidity' => 80, 'wind_speed' => 2,
        ]);
        $previous = $report->fresh()->getAttributes();
        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response($payload)]);
        Log::spy();

        $this->artisan('app:update-weather')->assertSuccessful();

        Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $message, array $context): bool => $context['reason'] === 'invalid_response'
        );
        $this->assertSame($previous, $report->fresh()->getAttributes());
    }

    public static function invalidPayloads(): array
    {
        return [
            'invalid json' => ['not JSON'],
            'missing current' => [[]],
            'partial readings' => [['current' => ['temperature_2m' => 25]]],
            'null readings' => [['current' => [
                'temperature_2m' => null, 'weather_code' => null,
                'relative_humidity_2m' => null, 'wind_speed_10m' => null,
            ]]],
            'invalid readings' => [['current' => [
                'temperature_2m' => 'unknown', 'weather_code' => 'cloudy',
                'relative_humidity_2m' => 101, 'wind_speed_10m' => -1,
            ]]],
        ];
    }

    public function test_recovery_updates_report_and_allows_logging_a_new_outage(): void
    {
        $this->freezeTime();
        Http::preventStrayRequests();
        Http::fakeSequence('api.open-meteo.com/*')
            ->pushFailedConnection()->pushFailedConnection()
            ->push($this->validPayload())
            ->pushFailedConnection()->pushFailedConnection();
        Sleep::fake();
        Log::spy();

        $this->artisan('app:update-weather')->assertSuccessful();
        $this->travel(11)->minutes();
        $this->artisan('app:update-weather')->assertSuccessful();
        $this->artisan('app:update-weather')->assertSuccessful();

        Http::assertSentCount(5);
        Log::shouldHaveReceived('warning')->twice();
        $this->assertDatabaseHas('weather_reports', ['id' => 1, 'temperature' => 24.5, 'weather_code' => 1]);
    }

    public function test_home_without_weather_does_not_repeat_requests_during_outage(): void
    {
        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::failedConnection()]);
        Sleep::fake();
        Log::spy();

        $this->get(route('home'))->assertOk();
        $this->get(route('home'))->assertOk();

        Http::assertSentCount(2);
        Log::shouldHaveReceived('warning')->once();
        $this->assertDatabaseCount('weather_reports', 0);
    }

    public function test_concurrent_update_is_skipped_without_calling_api(): void
    {
        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response($this->validPayload())]);
        $lock = Cache::lock('weather:update:lock', 60);
        $lock->get();

        try {
            $this->artisan('app:update-weather')->assertSuccessful();

            Http::assertNothingSent();
            $this->assertDatabaseCount('weather_reports', 0);
        } finally {
            $lock->release();
        }
    }

    public function test_unexpected_persistence_failure_is_not_suppressed_as_an_api_outage(): void
    {
        Http::preventStrayRequests();
        Http::fake(['api.open-meteo.com/*' => Http::response($this->validPayload())]);
        Event::listen('eloquent.saving: '.WeatherReport::class, function (): never {
            throw new RuntimeException('Persistence failed');
        });

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Persistence failed');

        $this->artisan('app:update-weather')->run();
    }

    /**
     * @return array{current: array<string, float|int>}
     */
    private function validPayload(): array
    {
        return ['current' => [
            'temperature_2m' => 24.5, 'weather_code' => 1,
            'relative_humidity_2m' => 85, 'wind_speed_10m' => 5.5,
        ]];
    }
}
