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
                <svg class="h-6 w-6 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M17.66 6.34l-1.41 1.41" />
                </svg>',
            1, 2 => '
                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path class="text-amber-500" d="M12 2v2M4.93 4.93l1.41 1.41M20 12h2M19.07 4.93l-1.41 1.41" />
                    <path class="text-amber-500" d="M15.947 12.65a4 4 0 00-5.925-4.128" />
                    <path class="text-blue-400" d="M13 22H7a5 5 0 1 1 4.9-6H13a3 3 0 0 1 0 6Z" />
                </svg>',
            3 => '
                <svg class="h-6 w-6 text-blue-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.5 19A3.5 3.5 0 0 0 21 15.5c0-2.79-2.54-4.5-5-4.5-.47 0-.89.09-1.25.26a5 5 0 1 0-8.75 3.74A3.5 3.5 0 0 0 9 21h8.5z" />
                </svg>',
            45, 48 => '
                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path class="text-gray-400" d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" />
                    <path class="text-gray-400" d="M16 17H7M17 21H9" />
                </svg>',
            51, 53, 55 => '
                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path class="text-blue-400" d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" />
                    <path class="text-blue-500" d="M8 19v2M12 21v2M16 19v2" />
                </svg>',
            61, 63, 65, 80, 81, 82 => '
                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path class="text-blue-400" d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" />
                    <path class="text-blue-500" d="M16 14v6M8 14v6M12 16v6" />
                </svg>',
            95, 96, 99 => '
                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path class="text-purple-400" d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" />
                    <path class="text-purple-500" d="m13 22-3-6h6l-3 6Z" fill="currentColor" />
                </svg>',
            default => '
                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path class="text-blue-400" d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" />
                    <path class="text-blue-500" d="M16 14v6M8 14v6M12 16v6" />
                </svg>',
        };
    }
}
