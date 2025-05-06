<?php

namespace App\Actions\Reservation;

use App\Enums\Status;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ConfirmReservation
{
    /**
     * Confirm a reservation.
     *
     * @param  Reservation  $reservation  The reservation to confirm
     * @return JsonResponse The confirmed reservation
     *
     * @throws Exception If the reservation confirmation fails
     */
    public function execute(Reservation $reservation): JsonResponse
    {
        try {
            // Update status to confirmed
            $reservation->status = Status::CONFIRMED;
            $reservation->save();

            // Load relationships
            $reservation->load(['guest', 'reservationable']);

            return (new ReservationResource($reservation))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Failed to confirm reservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to confirm reservation: '.$e->getMessage(), 500, $e);
        }
    }
}
