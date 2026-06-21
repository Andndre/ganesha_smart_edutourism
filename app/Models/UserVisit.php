<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserVisit extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'visitable_type', 'visitable_id', 'route_session_id', 'visited_at'];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function visitable(): MorphTo
    {
        return $this->morphTo();
    }

    public function routeSession(): BelongsTo
    {
        return $this->belongsTo(RouteSession::class);
    }
}
