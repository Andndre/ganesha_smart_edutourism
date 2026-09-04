<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedAudioNarration;
use App\Models\Concerns\HasMapLocation;
use App\Models\Concerns\HasSlug;
use App\Models\Concerns\HasTranslatableArrayOutput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug', 'short_description', 'description', 'category', 'place_type', 'is_detail', 'historical_images', 'audio_narration_paths'])]
class CulturalObject extends Model
{
    use HasFactory;
    use HasLocalizedAudioNarration;
    use HasMapLocation;
    use HasSlug;
    use HasTranslatableArrayOutput;
    use HasTranslations;

    public array $translatable = ['name', 'short_description', 'description'];

    /**
     * Preset jenis tempat yang memilih glyph pin di peta. Diturunkan dari inventaris
     * "Obyek Budaya Umum" desa, bukan daftar spekulatif. Label untuk form admin ada di
     * sebelah kanan; glyph-nya sendiri di map-pin-script.blade.php.
     *
     * Daftar ini juga jadi sumber aturan validasi, supaya preset tidak menyimpang
     * antara form dan request. Enum di database punya salinannya sendiri — kalau
     * menambah jenis baru, migrasi harus ikut diubah.
     */
    public const PLACE_TYPES = [
        'gerbang' => 'Gerbang / Angkul-angkul',
        'pura' => 'Pura / Merajan',
        'patung' => 'Patung',
        'bale' => 'Bale',
        'monumen' => 'Monumen / Relief',
        'koridor' => 'Koridor / Jalan Desa',
        'pekarangan' => 'Pekarangan Bertembok',
        'alam' => 'Alam',
        'rumah' => 'Rumah Tradisional',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'historical_images' => 'array',
            'audio_narration_paths' => 'array',
            'is_detail' => 'boolean',
        ];
    }

    /**
     * Get the map location for this cultural object.
     *
     * @return MorphOne<MapLocation>
     */
    public function mapLocation(): MorphOne
    {
        return $this->morphOne(MapLocation::class, 'locationable');
    }

    /**
     * Get every map point for this cultural object (e.g. multiple kulkul, padmasana).
     *
     * @return MorphMany<MapLocation>
     */
    public function mapLocations(): MorphMany
    {
        return $this->morphMany(MapLocation::class, 'locationable');
    }

    /**
     * Get the AR model owned by this cultural object.
     *
     * @return HasOne<ArModel>
     */
    public function arModel(): HasOne
    {
        return $this->hasOne(ArModel::class, 'cultural_object_id');
    }

    /**
     * Scope a query to only include objects with coordinates.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeWithCoordinates(Builder $query): Builder
    {
        return $query->whereHas('mapLocation');
    }

    /**
     * Scope a query to filter by category.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by AR availability.
     *
     * @param  Builder<CulturalObject>  $query
     * @return Builder<CulturalObject>
     */
    public function scopeWithAr(Builder $query): Builder
    {
        return $query->whereHas('arModel');
    }

    public function getArMarkerIdAttribute(): ?string
    {
        return $this->arModel?->ar_marker_id;
    }

    public function getArMarkerPattPathAttribute(): ?string
    {
        return $this->arModel?->ar_marker_patt_path;
    }

    public function getModel3dPathAttribute(): ?string
    {
        return $this->arModel?->model_3d_path;
    }

    public function getModel3dUsdzPathAttribute(): ?string
    {
        return $this->arModel?->model_3d_usdz_path;
    }

    /**
     * Get the favorites for this cultural object.
     *
     * @return MorphMany<UserFavorite>
     */
    public function favorites(): MorphMany
    {
        return $this->morphMany(UserFavorite::class, 'favoritable');
    }

    /**
     * Get the visits for this cultural object.
     *
     * @return MorphMany<UserVisit>
     */
    public function visits(): MorphMany
    {
        return $this->morphMany(UserVisit::class, 'visitable');
    }

    /**
     * Check if this cultural object is favorited by the given user.
     */
    public function isFavoritedBy(User $user): bool
    {
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if this cultural object is visited by the given user.
     */
    public function isVisitedBy(User $user): bool
    {
        return $this->visits()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the ratings for this cultural object.
     *
     * @return HasMany<CulturalObjectRating>
     */
    public function ratings(): HasMany
    {
        return $this->hasMany(CulturalObjectRating::class);
    }

    /**
     * Check if this cultural object is rated by the given user.
     */
    public function isRatedBy(User $user): bool
    {
        return $this->ratings()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the rating left by the given user, if any.
     */
    public function ratingBy(User $user): ?CulturalObjectRating
    {
        return $this->ratings()->where('user_id', $user->id)->first();
    }
}
