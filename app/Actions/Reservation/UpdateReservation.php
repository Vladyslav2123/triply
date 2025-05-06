<?php

namespace App\Actions\Reservation;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UpdateReservation
{
    /**
     * Update an existing reservation.
     *
     * @param  Reservation  $reservation  The reservation to update
     * @param  array  $data  The validated reservation data
     * @return JsonResponse The updated reservation
     *
     * @throws Exception If the reservation update fails
     */
    public function execute(Reservation $reservation, array $data): JsonResponse
    {
        try {
            // Update the reservation
            $reservation->update($data);

            // Load relationships
            $reservation->load(['guest', 'reservationable']);

            return (new ReservationResource($reservation))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Failed to update reservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw new Exception('Failed to update reservation: '.$e->getMessage(), 500, $e);
        }
    }
}
