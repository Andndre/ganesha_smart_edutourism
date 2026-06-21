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
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'nationality', 'preferred_language', 'avatar_path', 'google_id', 'email_verified_at'])]
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
     * Get the user's route sessions.
     *
     * @return HasMany<RouteSession>
     */
    public function routeSessions(): HasMany
    {
        return $this->hasMany(RouteSession::class);
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

    /**
     * Check if the user is a Ticket Officer.
     */
    public function isTicketOfficer(): bool
    {
        return $this->role === 'ticket_officer';
    }
}
