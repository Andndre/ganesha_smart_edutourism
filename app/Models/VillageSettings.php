<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillageSettings extends Model
{
    protected $table = 'village_settings';

    protected $fillable = ['open_time', 'close_time', 'id', 'house_cultural_object_id', 'featured_cultural_object_id'];

    /** @return BelongsTo<CulturalObject, $this> */
    public function houseCulturalObject(): BelongsTo
    {
        return $this->belongsTo(CulturalObject::class, 'house_cultural_object_id');
    }

    /** @return BelongsTo<CulturalObject, $this> */
    public function featuredCulturalObject(): BelongsTo
    {
        return $this->belongsTo(CulturalObject::class, 'featured_cultural_object_id');
    }

    protected function casts(): array
    {
        return [
            'open_time' => 'datetime:H:i',
            'close_time' => 'datetime:H:i',
            'house_cultural_object_id' => 'integer',
            'featured_cultural_object_id' => 'integer',
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
