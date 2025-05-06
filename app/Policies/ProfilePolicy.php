<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Profile $profile): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        if ($user->profile()->exists()) {
            return Response::deny('У вас вже є профіль.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Profile $profile): Response
    {
        if ($this->isOwnerOrAdmin($user, $profile)) {
            return Response::allow();
        }

        return Response::deny('Ви можете редагувати тільки свій профіль.');
    }

    /**
     * Check if the user is the owner of the profile or an admin.
     */
    private function isOwnerOrAdmin(User $user, Profile $profile): bool
    {
        return $user->id === $profile->user_id || $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Profile $profile): Response
    {
        if ($this->isOwnerOrAdmin($user, $profile)) {
            return Response::allow();
        }

        return Response::deny('Ви можете видалити тільки свій профіль.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Profile $profile): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('Тільки адміністратор може відновлювати видалені профілі.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Profile $profile): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('Тільки адміністратор може остаточно видаляти профілі.');
    }

    /**
     * Determine whether the user can verify the profile.
     */
    public function verify(User $user, Profile $profile): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('Тільки адміністратор може верифікувати профілі.');
    }

    /**
     * Determine whether the user can update superhost status.
     */
    public function updateSuperhostStatus(User $user, Profile $profile): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('Тільки адміністратор може змінювати статус superhost.');
    }
}
