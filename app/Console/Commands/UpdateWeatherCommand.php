<?php

namespace App\Console\Commands;

use App\Models\WeatherReport;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class UpdateWeatherCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-weather';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch current weather from Open-Meteo API and update the weather cache in the database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lock = Cache::lock('weather:update:lock', 60);

        if (! $lock->get()) {
            $this->info('Weather update already in progress; skipping.');

            return self::SUCCESS;
        }

        try {
            if (Cache::has('weather:update:cooldown')) {
                $this->info('Weather API is cooling down; keeping the last available report.');

                return self::SUCCESS;
            }

            return $this->updateWeather();
        } finally {
            $lock->release();
        }
    }

    private function updateWeather(): int
    {
        $latitude = config('services.penglipuran.latitude', -8.422303596762355);
        $longitude = config('services.penglipuran.longitude', 115.35948833933173);

        $this->info("Fetching weather forecast for coordinates: {$latitude}, {$longitude}...");

        $endpoint = 'https://api.open-meteo.com/v1/forecast';

        try {
            $response = Http::connectTimeout(3)
                ->timeout(10)
                ->retry(2, 500, fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->serverError() || $exception->response->status() === 429)), throw: false)
                ->get($endpoint, [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                    'timezone' => config('services.penglipuran.timezone', 'Asia/Makassar'),
                ]);
        } catch (ConnectionException $exception) {
            return $this->keepLastReport('connection_failed', ['message' => substr($exception->getMessage(), 0, 300)]);
        }

        if (! $response->successful()) {
            return $this->keepLastReport('http_error', ['status' => $response->status()]);
        }

        $validator = Validator::make(['current' => $response->json('current')], [
            'current' => ['required', 'array'],
            'current.temperature_2m' => ['required', 'numeric'],
            'current.weather_code' => ['required', 'integer'],
            'current.relative_humidity_2m' => ['required', 'integer', 'between:0,100'],
            'current.wind_speed_10m' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return $this->keepLastReport('invalid_response');
        }

        $current = $validator->validated()['current'];
        $temp = $current['temperature_2m'];
        $code = (int) $current['weather_code'];
        $humidity = $current['relative_humidity_2m'];
        $windSpeed = $current['wind_speed_10m'];
        $condition = WeatherReport::mapCodeToCondition($code);

        WeatherReport::updateOrCreate(['id' => 1], [
            'temperature' => $temp,
            'condition' => $condition,
            'weather_code' => $code,
            'humidity' => $humidity,
            'wind_speed' => $windSpeed,
        ]);

        Cache::forget('weather:update:warning');
        Cache::forget('weather:update:cooldown');

        $this->info('Weather cache updated successfully!');
        $this->info("Temperature: {$temp}°C, Condition: {$condition} (Code: {$code}), Humidity: {$humidity}%, Wind: {$windSpeed} km/h");

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int|string>  $context
     */
    private function keepLastReport(string $reason, array $context = []): int
    {
        Cache::put('weather:update:cooldown', true, now()->addMinutes(10));

        if (Cache::add('weather:update:warning', true, now()->addHour())) {
            Log::warning('Weather API unavailable; keeping the last available report and retrying in 10 minutes.', [
                'reason' => $reason,
                ...$context,
            ]);
        }

        $this->warn('Weather update skipped: '.$reason.'. Keeping the last available report.');

        return self::SUCCESS;
    }
}
