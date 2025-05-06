<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): Response
    {
        return $user->role === UserRole::ADMIN
            ? Response::allow()
            : Response::deny('Тільки адміністратори можуть переглядати список користувачів.');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): Response
    {
        // Admin can view any user
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        // Users can only view their own profile
        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('Ви можете переглядати тільки власний профіль.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): Response
    {
        return $user->role === UserRole::ADMIN
            ? Response::allow()
            : Response::deny('Тільки адміністратори можуть створювати користувачів.');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): Response
    {
        // Admin can update any user
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        // Users can only update their own profile
        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('Ви можете оновлювати тільки власний профіль.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): Response
    {
        // Admin can delete any user
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        // Users can delete their own account
        return $user->id === $model->id
            ? Response::allow()
            : Response::deny('Ви можете видаляти тільки власний профіль.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): Response
    {
        return $user->role === UserRole::ADMIN
            ? Response::allow()
            : Response::deny('Тільки адміністратори можуть відновлювати користувачів.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): Response
    {
        return $user->role === UserRole::ADMIN
            ? Response::allow()
            : Response::deny('Тільки адміністратори можуть остаточно видаляти користувачів.');
    }
}
