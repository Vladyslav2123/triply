<?php

namespace App\Actions\Reservation;

use App\Http\Resources\ReservationCollection;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetReservations
{
    /**
     * Get paginated reservations with optional filtering.
     *
     * @param  Request  $request  The request with filter parameters
     * @return ReservationCollection The paginated reservations
     *
     * @throws Exception If fetching reservations fails
     */
    public function execute(Request $request): ReservationCollection
    {
        try {
            $query = Reservation::query();

            // Apply filters if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by guest if user is not admin
            if (! $request->user()->can('viewAny', Reservation::class)) {
                $query->where('guest_id', $request->user()->id);
            }

            // Load relationships
            $query->with(['guest', 'reservationable', 'payment']);

            // Paginate results
            $perPage = $request->input('per_page', 15);
            $reservations = $query->paginate($perPage);

            return new ReservationCollection($reservations);
        } catch (Exception $e) {
            Log::error('Failed to get reservations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            throw new Exception('Failed to get reservations: '.$e->getMessage(), 500, $e);
        }
    }
}
