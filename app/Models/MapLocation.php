<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableArrayOutput;
use App\Services\HouseProfileService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'category', 'locationable_type', 'locationable_id', 'latitude', 'longitude', 'is_accessible', 'accessibility_notes'])]
class MapLocation extends Model
{
    protected static function booted(): void
    {
        static::saved(fn (self $location) => app(HouseProfileService::class)->synchronizeLocation($location));
    }

    use HasFactory;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['accessibility_notes'];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_accessible' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Get the owning locationable model.
     */
    public function locationable(): MorphTo
    {
        return $this->morphTo();
    }
}
