<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['session_id', 'user_id', 'event_type', 'event_data', 'latitude', 'longitude', 'device_type', 'browser', 'nationality', 'logged_at'])]
class VisitorLog extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_data' => 'array',
            'logged_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Get the user associated with this log.
     *
     * @return BelongsTo<User, VisitorLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by event type.
     *
     * @param  Builder<VisitorLog>  $query
     * @return Builder<VisitorLog>
     */
    public function scopeEventType(Builder $query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope a query to filter by session.
     *
     * @param  Builder<VisitorLog>  $query
     * @return Builder<VisitorLog>
     */
    public function scopeSession(Builder $query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope a query to filter by date range.
     *
     * @param  Builder<VisitorLog>  $query
     * @param  Carbon  $startDate
     * @param  Carbon  $endDate
     * @return Builder<VisitorLog>
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->whereBetween('logged_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by device type.
     *
     * @param  Builder<VisitorLog>  $query
     * @return Builder<VisitorLog>
     */
    public function scopeDevice(Builder $query, string $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope a query to get recent logs.
     *
     * @param  Builder<VisitorLog>  $query
     * @return Builder<VisitorLog>
     */
    public function scopeRecent(Builder $query, int $hours = 24)
    {
        return $query->where('logged_at', '>=', now()->subHours($hours));
    }
}
