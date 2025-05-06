<?php

namespace App\Actions\Reservation;

use App\Enums\Status;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CancelReservation
{
    /**
     * Cancel a reservation.
     *
     * @param  Reservation  $reservation  The reservation to cancel
     * @return JsonResponse The cancelled reservation
     *
     * @throws Exception If the reservation cancellation fails
     */
    public function execute(Reservation $reservation): JsonResponse
    {
        try {
            // Update status to cancelled
            $reservation->status = Status::CANCELLED;
            $reservation->save();

            // Load relationships
            $reservation->load(['guest', 'reservationable']);

            return (new ReservationResource($reservation))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Failed to cancel reservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to cancel reservation: '.$e->getMessage(), 500, $e);
        }
    }
}
