<?php

namespace App\Policies;

use App\Enums\ReservationStatus;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

class ReservationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin can view all reservations, others can only view their own
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Reservation $reservation): bool
    {
        // User can view if they are the guest
        if ($user->id === $reservation->guest_id) {
            return true;
        }

        // Check if user is the owner of the reservationable entity
        $reservationable = $reservation->reservationable;
        if ($reservationable && $this->isOwner($user, $reservationable)) {
            return true;
        }

        // Admin can view any reservation
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Check if the user is the owner of the reservationable entity.
     */
    private function isOwner(User $user, Model $model): bool
    {
        if (method_exists($model, 'host') && property_exists($model, 'host_id')) {
            return $model->host_id === $user->id;
        }

        // Check for any other model with user_id
        if (property_exists($model, 'user_id')) {
            return $model->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a reservation
        return true;
    }

    /**
     * Determine whether the user can create a reservation for a specific entity.
     */
    public function createFor(User $user, Model $reservationable): bool
    {
        // User cannot create a reservation for their own entity
        return ! $this->isOwner($user, $reservationable);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Reservation $reservation): Response
    {
        // Guest can update their own reservation if it's not completed
        if ($user->id === $reservation->guest_id) {
            if ($reservation->status === ReservationStatus::COMPLETED || $reservation->status === ReservationStatus::CANCELLED_BY_GUEST || $reservation->status === ReservationStatus::CANCELLED_BY_HOST) {
                return Response::deny('Завершені бронювання не можуть бути оновлені.');
            }

            return Response::allow();
        }

        // Owner of the reservationable entity can update the reservation
        $reservationable = $reservation->reservationable;
        if ($reservationable && $this->isOwner($user, $reservationable)) {
            return Response::allow();
        }

        // Admin can update any reservation
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('У вас немає дозволу на оновлення цього бронювання.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Reservation $reservation): Response
    {
        // Guest can delete their own reservation if it's pending
        if ($user->id === $reservation->guest_id) {
            if ($reservation->status !== ReservationStatus::PENDING) {
                return Response::deny('Тільки бронювання зі статусом "очікує" можуть бути видалені.');
            }

            return Response::allow();
        }

        // Admin can delete any reservation
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('У вас немає дозволу на видалення цього бронювання.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Reservation $reservation): bool
    {
        // Only admin can restore reservations
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Reservation $reservation): bool
    {
        // Only admin can force delete reservations
        return $user->role === UserRole::ADMIN;
    }

    /**
     * Determine whether the user can cancel the reservation.
     */
    public function cancel(User $user, Reservation $reservation): Response
    {
        // Guest can cancel their own reservation if it's not completed
        if ($user->id === $reservation->guest_id) {
            if ($reservation->status === ReservationStatus::COMPLETED) {
                return Response::deny('Завершені бронювання не можуть бути скасовані.');
            }

            return Response::allow();
        }

        // Owner of the reservationable entity can cancel the reservation
        $reservationable = $reservation->reservationable;
        if ($reservationable && $this->isOwner($user, $reservationable)) {
            return Response::allow();
        }

        // Admin can cancel any reservation
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('У вас немає дозволу на скасування цього бронювання.');
    }

    /**
     * Determine whether the user can confirm the reservation.
     */
    public function confirm(User $user, Reservation $reservation): Response
    {
        // Only the owner of the reservationable entity can confirm a reservation
        $reservationable = $reservation->reservationable;
        if ($reservationable && $this->isOwner($user, $reservationable)) {
            if ($reservation->status !== ReservationStatus::PENDING) {
                return Response::deny('Тільки бронювання зі статусом "очікує" можуть бути підтверджені.');
            }

            return Response::allow();
        }

        // Admin can confirm any reservation
        if ($user->role === UserRole::ADMIN) {
            return Response::allow();
        }

        return Response::deny('Тільки власник об\'єкту або адміністратор можуть підтвердити це бронювання.');
    }
}
