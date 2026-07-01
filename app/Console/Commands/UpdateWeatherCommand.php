<?php

namespace App\Console\Commands;

use App\Models\WeatherReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

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
        $latitude = config('services.penglipuran.latitude', -8.422303596762355);
        $longitude = config('services.penglipuran.longitude', 115.35948833933173);

        $this->info("Fetching weather forecast for coordinates: {$latitude}, {$longitude}...");

        $endpoint = 'https://api.open-meteo.com/v1/forecast';

        try {
            $response = Http::get($endpoint, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m',
                'timezone' => 'Asia/Singapore',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $current = $data['current'] ?? null;

                if (! $current) {
                    $this->error('Failed to parse weather data. "current" block missing.');

                    return CommandAlias::FAILURE;
                }

                $temp = $current['temperature_2m'];
                $code = $current['weather_code'];
                $humidity = $current['relative_humidity_2m'];
                $windSpeed = $current['wind_speed_10m'];
                $condition = WeatherReport::mapCodeToCondition($code);

                // Cache in the database as a single global record
                WeatherReport::updateOrCreate(
                    ['id' => 1], // Always reuse ID 1
                    [
                        'temperature' => $temp,
                        'condition' => $condition,
                        'weather_code' => $code,
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                    ]
                );

                $this->info('Weather cache updated successfully!');
                $this->info("Temperature: {$temp}°C, Condition: {$condition} (Code: {$code}), Humidity: {$humidity}%, Wind: {$windSpeed} km/h");

                return CommandAlias::SUCCESS;
            }

            $this->error('Failed to fetch weather from API. Status: '.$response->status());
            Log::error('Weather API Error: '.$response->body());

            return CommandAlias::FAILURE;
        } catch (\Exception $e) {
            $this->error('Weather API Exception: '.$e->getMessage());
            Log::error('Weather API Exception: '.$e->getMessage()."\n".$e->getTraceAsString());

            return CommandAlias::FAILURE;
        }
    }
}
