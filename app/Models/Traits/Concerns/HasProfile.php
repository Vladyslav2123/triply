<?php

namespace App\Models\Traits\Concerns;

use App\Models\Profile;

trait HasProfile
{
    /**
     * Get the user's profile or create a new one if it doesn't exist.
     */
    public function getOrCreateProfile(): Profile
    {
        $profile = $this->profile;

        if (! $profile) {
            $profile = $this->profile()->create();
            $this->load('profile');
        }

        return $profile;
    }
}
