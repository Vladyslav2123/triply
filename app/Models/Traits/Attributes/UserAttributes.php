<?php

namespace App\Models\Traits\Attributes;

/**
 * Trait UserAttributes
 */
trait UserAttributes
{
    /**
     * Get the full name attribute from the profile.
     */
    protected function getFullNameAttribute(): string
    {
        if ($this->profile) {
            return $this->profile->full_name;
        }

        return 'Unnamed User';
    }

    /**
     * Get the URL attribute for the user's profile.
     */
    protected function getUrlAttribute(): string
    {
        return route('profiles.show', $this->slug);
    }

    /**
     * Get the average rating attribute.
     */
    protected function getAverageRatingAttribute(): float
    {
        return $this->getAverageRating();
    }

    /**
     * Get the reviews count attribute.
     */
    protected function getReviewsCountAttribute(): int
    {
        return $this->getReviewsCount();
    }

    /**
     * Get the unread messages count attribute.
     */
    protected function getUnreadMessagesCountAttribute(): int
    {
        return $this->getUnreadMessagesCount();
    }

    /**
     * Get the is verified attribute.
     */
    protected function getIsVerifiedAttribute(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Get the joined date attribute (formatted created_at).
     */
    protected function getJoinedDateAttribute(): string
    {
        return $this->created_at->format('F Y');
    }
}
