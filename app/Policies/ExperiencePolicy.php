<?php

namespace App\Policies;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExperiencePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view experiences, even guests
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Experience $experience): bool
    {
        return true; // Anyone can view an experience, even guests
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->isHost() || $user->isAdmin()
            ? Response::allow()
            : Response::deny('Only hosts and administrators can create experiences.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Experience $experience): Response
    {
        // Admin can update any experience
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Host can only update their own experiences
        return $user->id === $experience->host_id
            ? Response::allow()
            : Response::deny('You can only update your own experiences.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Experience $experience): Response
    {
        // Admin can delete any experience
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Host can only delete their own experiences
        return $user->id === $experience->host_id
            ? Response::allow()
            : Response::deny('You can only delete your own experiences.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Experience $experience): Response
    {
        // Admin can restore any experience
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Host can only restore their own experiences
        return $user->id === $experience->host_id
            ? Response::allow()
            : Response::deny('You can only restore your own experiences.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Experience $experience): Response
    {
        // Only admins can permanently delete experiences
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Only administrators can permanently delete experiences.');
    }

    /**
     * Determine whether the user can publish an experience.
     */
    public function publish(User $user, Experience $experience): Response
    {
        // Admin can publish any experience
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Host can only publish their own experiences
        return $user->id === $experience->host_id
            ? Response::allow()
            : Response::deny('You can only publish your own experiences.');
    }

    /**
     * Determine whether the user can unpublish an experience.
     */
    public function unpublish(User $user, Experience $experience): Response
    {
        // Admin can unpublish any experience
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Host can only unpublish their own experiences
        return $user->id === $experience->host_id
            ? Response::allow()
            : Response::deny('You can only unpublish your own experiences.');
    }
}
