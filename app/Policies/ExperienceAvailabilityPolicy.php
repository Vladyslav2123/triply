<?php

namespace App\Policies;

use App\Models\ExperienceAvailability;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExperienceAvailabilityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view availability dates
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, ExperienceAvailability $experienceAvailability): bool
    {
        return true; // Anyone can view specific availability dates
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->isHost() || $user->isAdmin()
            ? Response::allow()
            : Response::deny('Only hosts and administrators can create availability dates.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExperienceAvailability $experienceAvailability): Response
    {
        // Admin can update any availability
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Host can only update availability for their own experiences
        $experience = $experienceAvailability->experience;

        return $user->id === $experience->host_id
            ? Response::allow()
            : Response::deny('You can only update availability for your own experiences.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExperienceAvailability $experienceAvailability): Response
    {
        // Admin can delete any availability
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Host can only delete availability for their own experiences
        $experience = $experienceAvailability->experience;

        return $user->id === $experience->host_id
            ? Response::allow()
            : Response::deny('You can only delete availability for your own experiences.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExperienceAvailability $experienceAvailability): Response
    {
        // Admin can restore any availability
        if ($user->isAdmin()) {
            return Response::allow();
        }

        // Host can only restore availability for their own experiences
        $experience = $experienceAvailability->experience;

        return $user->id === $experience->host_id
            ? Response::allow()
            : Response::deny('You can only restore availability for your own experiences.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExperienceAvailability $experienceAvailability): Response
    {
        // Only admins can permanently delete availability
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Only administrators can permanently delete availability dates.');
    }
}
