<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class FavoritePolicy
{
    /**
     * Визначає, чи може користувач переглядати всі обрані.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Визначає, чи може користувач переглядати конкретний обраний запис.
     */
    public function view(User $user, Favorite $favorite): Response
    {
        return $this->checkOwnershipOrAdmin($user, $favorite)
            ? Response::allow()
            : Response::deny('Ви можете переглядати лише власні обрані записи.');
    }

    /**
     * Перевіряє, чи є користувач власником обраного запису або адміністратором.
     */
    private function checkOwnershipOrAdmin(User $user, Favorite $favorite): bool
    {
        return $user->id === $favorite->user_id || $user->role === UserRole::ADMIN;
    }

    /**
     * Визначає, чи може користувач створювати обрані записи.
     */
    public function create(User $user): bool
    {
        return true; // Authenticated users can create favorites (middleware handles authentication)
    }

    /**
     * Визначає, чи може користувач створити обраний запис для конкретного об'єкту.
     */
    public function createFor(User $user, Model $favoriteable): Response
    {
        if (! in_array(get_class($favoriteable), array_values(Relation::morphMap()))) {
            return Response::deny('Непідтримуваний тип об\'єкту для обраного.');
        }

        // Check if the user already has this item in favorites
        if ($user->hasFavorited($favoriteable)) {
            return Response::deny('Цей об\'єкт вже додано до обраних.');
        }

        return Response::allow();
    }

    /**
     * Визначає, чи може користувач оновлювати обраний запис.
     * Оновлення не дозволені для обраних записів, оскільки вони є простими перемикачами.
     */
    public function update(User $user, Favorite $favorite): bool
    {
        return false;
    }

    /**
     * Визначає, чи може користувач видалити обраний запис.
     */
    public function delete(User $user, Favorite $favorite): Response
    {
        return $this->checkOwnershipOrAdmin($user, $favorite)
            ? Response::allow()
            : Response::deny('Ви можете видаляти лише власні обрані записи.');
    }

    /**
     * Визначає, чи може користувач відновити обраний запис.
     * Відновлення не підтримується для обраних записів.
     */
    public function restore(User $user, Favorite $favorite): bool
    {
        return false;
    }

    /**
     * Визначає, чи може користувач остаточно видалити обраний запис.
     * Примусове видалення не підтримується для обраних записів.
     */
    public function forceDelete(User $user, Favorite $favorite): bool
    {
        return false;
    }
}
