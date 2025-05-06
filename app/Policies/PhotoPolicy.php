<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class PhotoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view photos
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Photo $photo): bool
    {
        return true; // Anyone can view photos
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, $photoableModel = null): bool
    {
        if ($photoableModel) {
            return $this->createFor($user, $photoableModel)->allowed();
        }

        return true; // Authenticated users can create photos (middleware handles authentication)
    }

    /**
     * Determine whether the user can create a photo for a specific photoable entity.
     */
    public function createFor(User $user, Model $photoable): Response
    {
        $morphMap = Relation::morphMap();
        $photoableType = array_search(get_class($photoable), $morphMap) ?: strtolower(class_basename($photoable));

        if (! $photoableType || ! in_array($photoableType, ['user', 'profile', 'listing', 'experience'])) {
            return Response::deny('Непідтримуваний тип об\'єкту для фото.');
        }

        // If uploading photo for a user, check if it's the same user or admin
        if ($photoable instanceof User) {
            if ($user->id !== $photoable->id && $user->role !== UserRole::ADMIN) {
                return Response::deny('Ви можете завантажувати фото тільки для свого профілю.');
            }

            return Response::allow();
        }

        // If uploading photo for a profile, check if it's the same user's profile or admin
        if ($photoableType === 'profile') {
            if ($photoable->user_id !== $user->id && $user->role !== UserRole::ADMIN) {
                return Response::deny('Ви можете завантажувати фото тільки для свого профілю.');
            }

            return Response::allow();
        }

        // For listings and experiences, check if user is the owner or admin
        if (method_exists($photoable, 'isOwnedBy') && ! $photoable->isOwnedBy($user) && $user->role !== UserRole::ADMIN) {
            return Response::deny('Ви можете завантажувати фото тільки для власних об\'єктів.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Photo $photo): Response
    {
        return $this->update($user, $photo);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Photo $photo): Response
    {
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        $photoable = $photo->photoable;

        if (! $photoable) {
            return Response::deny('Фото не прив\'язане до жодного об\'єкту.');
        }

        // If it's a user photo, check if it's the same user
        if ($photoable instanceof User) {
            return $user->id === $photoable->id
                ? Response::allow()
                : Response::deny('Ви можете оновлювати тільки власні фото.');
        }

        // If it's a profile photo, check if it's the same user's profile
        if (property_exists($photoable, 'user_id')) {
            return $user->id === $photoable->user_id
                ? Response::allow()
                : Response::deny('Ви можете оновлювати тільки власні фото.');
        }

        // For listings and experiences, check if user is the owner
        if (method_exists($photoable, 'isOwnedBy') && $photoable->isOwnedBy($user)) {
            return Response::allow();
        }

        return Response::deny('Ви можете оновлювати фото тільки для власних об\'єктів.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Photo $photo): Response
    {
        if ($user->role !== UserRole::ADMIN) {
            return Response::deny('Тільки адміністратор може відновлювати видалені фото.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Photo $photo): Response
    {
        if ($user->role !== UserRole::ADMIN) {
            return Response::deny('Тільки адміністратор може остаточно видалити фото.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can set the photo as primary.
     */
    public function setPrimary(User $user, Photo $photo): Response
    {
        return $this->update($user, $photo);
    }
}
