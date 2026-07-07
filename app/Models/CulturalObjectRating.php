<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cultural_object_id', 'user_id', 'rating', 'comment'])]
class CulturalObjectRating extends Model
{
    use HasFactory;

    public function culturalObject(): BelongsTo
    {
        return $this->belongsTo(CulturalObject::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
