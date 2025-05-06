<?php

namespace App\Actions\Reservation;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GetReservation
{
    /**
     * Get a specific reservation with its relationships.
     *
     * @param  Reservation  $reservation  The reservation to get
     * @return JsonResponse The reservation with loaded relationships
     *
     * @throws Exception If fetching the reservation fails
     */
    public function execute(Reservation $reservation): JsonResponse
    {
        try {
            // Load relationships
            $reservation->with(['guest', 'reservationable', 'payment', 'review']);

            return (new ReservationResource($reservation))
                ->response()
                ->setStatusCode(Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Failed to get reservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to get reservation: '.$e->getMessage(), 500, $e);
        }
    }
}
