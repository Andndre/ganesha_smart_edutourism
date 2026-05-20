<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'nationality', 'preferred_language', 'avatar_path'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Get the attributes that should be cast.
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
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the user's learning progress.
     */
    public function learningProgress(): HasMany
    {
        return $this->hasMany(UserLearningProgress::class);
    }

    /**
     * Get the user's feedbacks.
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
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
