<?php

namespace App\Actions\Reservation;

use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CreateReservation
{
    /**
     * Create a new reservation.
     *
     * @param  array  $data  The validated reservation data
     * @return JsonResponse The created reservation
     *
     * @throws Exception If the reservation creation fails
     */
    public function execute(array $data): JsonResponse
    {
        try {
            // Create the reservation with validated data
            $reservation = Reservation::create($data);

            // Load relationships
            $reservation->load(['guest', 'reservationable']);

            return (new ReservationResource($reservation))
                ->response()
                ->setStatusCode(Response::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('Failed to create reservation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw new Exception('Failed to create reservation: '.$e->getMessage(), 500, $e);
        }
    }
}
