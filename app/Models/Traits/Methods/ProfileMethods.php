<?php

namespace App\Models\Traits\Methods;

/**
 * Trait ProfileMethods
 *
 * Містить всі методи для моделі Profile.
 */
trait ProfileMethods
{
    /**
     * Check if the profile is verified.
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Verify the profile.
     */
    public function verify(string $method = 'manual'): void
    {
        $this->is_verified = true;
        $this->verified_at = now();
        $this->verification_method = $method;
        $this->save();
    }

    /**
     * Unverify the profile.
     */
    public function unverify(): void
    {
        $this->is_verified = false;
        $this->verified_at = null;
        $this->verification_method = null;
        $this->save();
    }

    /**
     * Increment the profile's views count.
     */
    public function incrementViewsCount(): void
    {
        $this->increment('views_count');
        $this->save();
    }

    /**
     * Update the profile's last active timestamp.
     */
    public function updateLastActive(): void
    {
        $this->last_active_at = now();
        $this->save();
    }
}
