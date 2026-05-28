<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['temperature', 'condition', 'weather_code', 'humidity', 'wind_speed'])]
class WeatherReport extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'weather_code' => 'integer',
            'humidity' => 'integer',
            'wind_speed' => 'decimal:2',
        ];
    }

    /**
     * Map WMO weather code to user-friendly Indonesian condition text.
     */
    public static function mapCodeToCondition(int $code): string
    {
        return match ($code) {
            0 => 'Cerah',
            1, 2 => 'Cerah Berawan',
            3 => 'Berawan',
            45, 48 => 'Berkabut',
            51, 53, 55 => 'Gerimis',
            61, 63 => 'Hujan Ringan',
            65 => 'Hujan Lebat',
            80, 81, 82 => 'Hujan Deras',
            95, 96, 99 => 'Hujan Badai',
            default => 'Hujan',
        };
    }

    /**
     * Get the beautiful Tailwind-styled SVG icon for this weather report.
     */
    public function getIconHtml(): string
    {
        return match ($this->weather_code) {
            0 => '
                <svg class="h-6 w-6 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>',
            1, 2, 3 => '
                <svg class="h-6 w-6 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                </svg>',
            45, 48 => '
                <svg class="h-6 w-6 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>',
            51, 53, 55, 61, 63, 65, 80, 81, 82 => '
                <svg class="h-6 w-6 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14a5 5 0 01-9.563 2.036A4 4 0 018 12a4 4 0 01-.017-.362M9 19h.01M11 21h.01M13 19h.01M15 21h.01M17 19h.01M19 21h.01" />
                </svg>',
            95, 96, 99 => '
                <svg class="h-6 w-6 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>',
            default => '
                <svg class="h-6 w-6 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 14a5 5 0 01-9.563 2.036A4 4 0 018 12a4 4 0 01-.017-.362M9 19h.01M11 21h.01M13 19h.01M15 21h.01M17 19h.01M19 21h.01" />
                </svg>',
        };
    }
}
