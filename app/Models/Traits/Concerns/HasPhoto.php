<?php

namespace App\Models\Traits\Concerns;

trait HasPhoto
{
    /**
     * Get the user's photo URL or a default avatar.
     */
    public function getUrlAttribute(): string
    {
        if ($this->hasPhoto() && $this->photo->url) {
            return $this->photo->url;
        }

        return asset('images/default-avatar.png');
    }

    /**
     * Check if the user has a photo.
     */
    public function hasPhoto(): bool
    {
        return $this->photo()->exists();
    }
}
