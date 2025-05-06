<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ListingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view listings, even guests
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Listing $listing): bool
    {
        return true; // Anyone can view a listing, even guests
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->role === UserRole::HOST || $user->role === UserRole::ADMIN
            ? Response::allow()
            : Response::deny('Тільки хости та адміністратори можуть створювати оголошення.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Listing $listing): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return $user->id === $listing->host_id
            ? Response::allow()
            : Response::deny('Ви можете редагувати тільки власні оголошення.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Listing $listing): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return $user->id === $listing->host_id
            ? Response::allow()
            : Response::deny('Ви можете видаляти тільки власні оголошення.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Listing $listing): Response
    {
        if ($user->role !== UserRole::ADMIN) {
            return Response::deny('Тільки адміністратор може відновлювати видалені оголошення.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Listing $listing): Response
    {
        if ($user->role !== UserRole::ADMIN) {
            return Response::deny('Тільки адміністратор може остаточно видалити оголошення.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can publish the listing.
     */
    public function publish(User $user, Listing $listing): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        if ($user->id !== $listing->host_id) {
            return Response::deny('Ви можете публікувати тільки власні оголошення.');
        }

        // Additional checks can be added here (e.g., listing completeness)
        return Response::allow();
    }

    /**
     * Determine whether the user can unpublish the listing.
     */
    public function unpublish(User $user, Listing $listing): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return $user->id === $listing->host_id
            ? Response::allow()
            : Response::deny('Ви можете знімати з публікації тільки власні оголошення.');
    }
}
