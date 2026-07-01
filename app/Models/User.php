<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'nationality', 'preferred_language', 'avatar_path', 'google_id', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPushSubscriptions, Notifiable;

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
            'role' => UserRole::class,
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
     * Get the user's favorites.
     *
     * @return HasMany<UserFavorite>
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Get the user's visits.
     *
     * @return HasMany<UserVisit>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(UserVisit::class);
    }

    /**
     * Get all favorited models as a collection.
     */
    public function favoriteItems(): Collection
    {
        return $this->favorites()->with('favoritable')->get()->pluck('favoritable');
    }

    /**
     * Get all visited models as a collection.
     */
    public function visitedItems(): Collection
    {
        return $this->visits()->with('visitable')->get()
            ->pluck('visitable')
            ->filter()
            ->values();
    }

    /**
     * Check if the user has favorited a specific model.
     */
    public function hasFavorited($favoritable): bool
    {
        return $this->favorites()
            ->where('favoritable_type', $favoritable->getMorphClass())
            ->where('favoritable_id', $favoritable->id)
            ->exists();
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
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Check if the user is a UMKM owner.
     */
    public function isUmkmOwner(): bool
    {
        return $this->role === UserRole::UmkmOwner;
    }

    /**
     * Check if the user is a Ticket Officer.
     */
    public function isTicketOfficer(): bool
    {
        return $this->role === UserRole::TicketOfficer;
    }

    /**
     * Get the URL for the user's avatar.
     * Falls back to ui-avatars.com if no avatar is set.
     */
    public function avatarUrl(): string
    {
        if ($this->avatar_path) {
            // External URL (e.g., Google avatar) - use directly
            if (str_starts_with($this->avatar_path, 'http')) {
                return $this->avatar_path;
            }

            return Storage::disk('public')->url($this->avatar_path);
        }

        return 'https://ui-avatars.com/api/?name='.\urlencode($this->name).'&background=D4AF37&color=fff&bold=true';
    }
}
