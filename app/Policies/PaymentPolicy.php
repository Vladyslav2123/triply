<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can view payments list
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Payment $payment): Response
    {
        // User can view payment if they are the guest who made the reservation
        if ($user->id === $payment->reservation->guest_id) {
            return Response::allow();
        }

        // Host can view payment if it's for their listing/experience
        $reservationable = $payment->reservation->reservationable;
        if ($reservationable && property_exists($reservationable, 'host_id') && $reservationable->host_id === $user->id) {
            return Response::allow();
        }

        // Admin can view any payment
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to view this payment.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create payments
    }

    /**
     * Determine whether the user can create a payment for a specific reservation.
     */
    public function createForReservation(User $user, Reservation $reservation): Response
    {
        // Only the guest who made the reservation can create a payment for it
        if ($user->id !== $reservation->guest_id) {
            return Response::deny('You can only make payments for your own reservations.');
        }

        // Cannot create payment for cancelled reservations
        if ($reservation->isCancelled()) {
            return Response::deny('Cannot make payment for a cancelled reservation.');
        }

        // Check if reservation is already fully paid
        if ($reservation->isFullyPaid()) {
            return Response::deny('This reservation is already fully paid.');
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Payment $payment): Response
    {
        // Only admins can update payments
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Only administrators can update payments.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Payment $payment): Response
    {
        // Only admins can delete payments
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Only administrators can delete payments.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Payment $payment): Response
    {
        // Only admins can restore payments
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Only administrators can restore payments.');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Payment $payment): Response
    {
        // Only admins can force delete payments
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Only administrators can permanently delete payments.');
    }

    /**
     * Determine whether the user can view payment statistics.
     */
    public function viewStatistics(User $user): Response
    {
        // Only hosts and admins can view payment statistics
        if ($user->isHost() || $user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('Only hosts and administrators can view payment statistics.');
    }

    /**
     * Determine whether the user can generate a receipt for a payment.
     */
    public function generateReceipt(User $user, Payment $payment): Response
    {
        // User can generate receipt if they are the guest who made the reservation
        if ($user->id === $payment->reservation->guest_id) {
            return Response::allow();
        }

        // Host can generate receipt if it's for their listing/experience
        $reservationable = $payment->reservation->reservationable;
        if ($reservationable && property_exists($reservationable, 'host_id') && $reservationable->host_id === $user->id) {
            return Response::allow();
        }

        // Admin can generate any receipt
        if ($user->isAdmin()) {
            return Response::allow();
        }

        return Response::deny('You do not have permission to generate a receipt for this payment.');
    }
}
