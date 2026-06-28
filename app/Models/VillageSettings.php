<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillageSettings extends Model
{
    protected $table = 'village_settings';

    protected $fillable = ['open_time', 'close_time'];

    protected function casts(): array
    {
        return [
            'open_time'  => 'datetime:H:i',
            'close_time' => 'datetime:H:i',
        ];
    }

    public static function get(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['open_time' => '08:00:00', 'close_time' => '18:00:00']
        );
    }
}
