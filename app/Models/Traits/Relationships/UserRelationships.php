<?php

namespace App\Models\Traits\Relationships;

use App\Models\Experience;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Profile;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait UserRelationships
 *
 * Містить всі відносини для моделі User.
 */
trait UserRelationships
{
    /**
     * Get the user's listings relationship.
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'host_id');
    }

    /**
     * Get the user's experiences relationship.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class, 'host_id');
    }

    /**
     * Get the user's reservations relationship.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'guest_id');
    }

    /**
     * Get the user's reviews relationship.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    /**
     * Get the profile relationship.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the user's sent messages relationship.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the user's received messages relationship.
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }
}
