<?php

namespace App\Policies;

use App\Models\ListingAvailability;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ListingAvailabilityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view availability, even guests
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, ListingAvailability $listingAvailability): bool
    {
        return true; // Anyone can view availability, even guests
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        // Check if the user is the host of the listing or an admin
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // For hosts, we need to check if they own the listing
        // This requires the listing_id to be passed in the request
        $listingId = request('listing_id');
        if (! $listingId) {
            return Response::deny('Listing ID is required.');
        }

        $listing = \App\Models\Listing::find($listingId);
        if (! $listing) {
            return Response::deny('Listing not found.');
        }

        return $user->id === $listing->host_id
            ? Response::allow()
            : Response::deny('You can only manage availability for your own listings.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ListingAvailability $listingAvailability): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return $user->id === $listingAvailability->listing->host_id
            ? Response::allow()
            : Response::deny('You can only update availability for your own listings.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ListingAvailability $listingAvailability): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return $user->id === $listingAvailability->listing->host_id
            ? Response::allow()
            : Response::deny('You can only delete availability for your own listings.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ListingAvailability $listingAvailability): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return $user->id === $listingAvailability->listing->host_id
            ? Response::allow()
            : Response::deny('You can only restore availability for your own listings.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ListingAvailability $listingAvailability): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return $user->id === $listingAvailability->listing->host_id
            ? Response::allow()
            : Response::deny('You can only permanently delete availability for your own listings.');
    }

    /**
     * Determine whether the user can bulk update availability.
     */
    public function bulkUpdate(User $user, string $listingId): Response
    {
        if ($user->isAdmin()) {
            return Response::allow();
        }

        $listing = \App\Models\Listing::find($listingId);
        if (! $listing) {
            return Response::deny('Listing not found.');
        }

        return $user->id === $listing->host_id
            ? Response::allow()
            : Response::deny('You can only bulk update availability for your own listings.');
    }
}
