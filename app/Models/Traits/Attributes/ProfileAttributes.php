<?php

namespace App\Models\Traits\Attributes;

use App\ValueObjects\Location;
use Throwable;

/**
 * Trait ProfileAttributes
 */
trait ProfileAttributes
{
    /**
     * Get the full name of the profile owner.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name) ?: 'Unnamed User';
    }

    /**
     * Get the formatted address.
     */
    public function getAddressFormattedAttribute(): string
    {
        $location = $this->location;
        if (! $location) {
            return '-';
        }

        if (! $location instanceof Location) {
            return '-';
        }

        try {
            return sprintf(
                '%s, %s, %s',
                $location->address->street,
                $location->address->city,
                $location->address->country
            );
        } catch (Throwable $e) {
            return (string) $location;
        }
    }

    /**
     * Get the social media links as an array.
     *
     * @return array<string, string|null>
     */
    public function getSocialLinksAttribute(): array
    {
        return [
            'facebook' => $this->facebook_url,
            'instagram' => $this->instagram_url,
            'twitter' => $this->twitter_url,
            'linkedin' => $this->linkedin_url,
        ];
    }

    /**
     * Get the age attribute.
     */
    public function getAgeAttribute(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }

        return $this->birth_date->age;
    }

    /**
     * Get the is superhost attribute.
     */
    protected function getIsSuperhostAttribute(): bool
    {
        return $this->user->getAverageRating() >= 4.8 &&
            $this->user->listings()->has('reservations', '>=', 10)->exists();
    }
}
