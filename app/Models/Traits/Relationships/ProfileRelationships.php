<?php

namespace App\Models\Traits\Relationships;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait ProfileRelationships
 *
 * Містить всі відносини для моделі Profile.
 */
trait ProfileRelationships
{
    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the profile's photo.
     */
    public function photo(): MorphOne
    {
        return $this->morphOne(Photo::class, 'photoable');
    }
}
