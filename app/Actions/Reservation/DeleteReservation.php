<?php

namespace App\Actions\Reservation;

use App\Models\Reservation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DeleteReservation
{
    /**
     * Delete a reservation.
     *
     * @param  Reservation  $reservation  The reservation to delete
     * @return JsonResponse Empty response with 204 status code
     *
     * @throws Exception If the reservation deletion fails
     */
    public function execute(Reservation $reservation): JsonResponse
    {
        try {
            // Delete the reservation
            $reservation->delete();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            Log::error('Failed to delete reservation', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to delete reservation: '.$e->getMessage(), 500, $e);
        }
    }
}
