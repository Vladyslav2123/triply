<?php

namespace App\Models\Traits\Concerns;

use App\Models\Favorite;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasFavorites
{
    /**
     * Add a listing to user's favorites.
     */
    public function addToFavorites(Listing $listing): void
    {
        if (! $this->hasFavorited($listing)) {
            $this->favorites()->create([
                'favoriteable_id' => $listing->id,
                'favoriteable_type' => get_class($listing),
            ]);
        }
    }

    /**
     * Check if the user has favorited a specific listing.
     */
    public function hasFavorited(Listing $listing): bool
    {
        return $this->favorites()->where('favoriteable_id', $listing->id)
            ->where('favoriteable_type', get_class($listing))
            ->exists();
    }

    /**
     * Get the user's favorites relationship.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'user_id');
    }

    /**
     * Remove a listing from user's favorites.
     */
    public function removeFromFavorites(Listing $listing): void
    {
        $this->favorites()->where('favoriteable_id', $listing->id)
            ->where('favoriteable_type', get_class($listing))
            ->delete();
    }
}
