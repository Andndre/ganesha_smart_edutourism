<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * User model representing authenticated users in the system.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $role
 * @property string|null $phone
 * @property string|null $nationality
 * @property string|null $preferred_language
 * @property string|null $avatar_path
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password', 'role', 'phone', 'nationality', 'preferred_language', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the cultural object associated with the user (if they're a cultural heritage owner).
     */
    public function culturalObject(): HasOne
    {
        return $this->hasOne(CulturalObject::class);
    }

    /**
     * Get the UMKM profile associated with the user.
     */
    public function umkmProfile(): HasOne
    {
        return $this->hasOne(UmkmProfile::class);
    }

    /**
     * Get the user's reservations.
     *
     * @return HasMany<Reservation>
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the user's learning progress.
     *
     * @return HasMany<UserLearningProgress>
     */
    public function learningProgress(): HasMany
    {
        return $this->hasMany(UserLearningProgress::class);
    }

    /**
     * Get the user's feedbacks.
     *
     * @return HasMany<Feedback>
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Scope a query to only include admin users.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeAdmins(Builder $query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope a query to only include active users.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeActive(Builder $query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a UMKM owner.
     */
    public function isUmkmOwner(): bool
    {
        return $this->role === 'umkm_owner';
    }
}
