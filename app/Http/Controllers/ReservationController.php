<?php

namespace App\Http\Controllers;

use App\Actions\Reservation\CancelReservation;
use App\Actions\Reservation\ConfirmReservation;
use App\Actions\Reservation\CreateReservation;
use App\Actions\Reservation\DeleteReservation;
use App\Actions\Reservation\GetReservation;
use App\Actions\Reservation\GetReservations;
use App\Actions\Reservation\UpdateReservation;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Resources\ReservationCollection;
use App\Models\Reservation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Reservations",
 *     description="API Endpoints for managing reservations"
 * )
 */
class ReservationController extends Controller
{
    /**
     * Display a listing of the reservations.
     *
     * @OA\Get(
     *     path="/api/reservations",
     *     operationId="getReservations",
     *     tags={"Reservations"},
     *     summary="Get list of reservations",
     *     description="Returns list of reservations with pagination",
     *     security={"bearerAuth": {}},
     *
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by reservation status",
     *         required=false,
     *
     *         @OA\Schema(type="string", enum={"pending", "confirmed", "cancelled", "completed"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Reservation")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request, GetReservations $action): ReservationCollection
    {
        return $action->execute($request);
    }

    /**
     * Store a newly created reservation in storage.
     *
     * @OA\Post(
     *     path="/api/reservations",
     *     operationId="storeReservation",
     *     tags={"Reservations"},
     *     summary="Create a new reservation",
     *     description="Creates a new reservation and returns it",
     *     security={"bearerAuth": {}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StoreReservationRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreReservationRequest $request, CreateReservation $action): JsonResponse
    {
        return $action->execute($request->validatedWithDefaults());
    }

    /**
     * Display the specified reservation.
     *
     * @OA\Get(
     *     path="/api/reservations/{id}",
     *     operationId="getReservation",
     *     tags={"Reservations"},
     *     summary="Get reservation details",
     *     description="Returns details of a specific reservation",
     *     security={"bearerAuth": {}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Resource not found")
     * )
     */
    public function show(Reservation $reservation, GetReservation $action): JsonResponse
    {
        $this->authorize('view', $reservation);

        return $action->execute($reservation);
    }

    /**
     * Update the specified reservation in storage.
     *
     * @OA\Put(
     *     path="/api/reservations/{id}",
     *     operationId="updateReservation",
     *     tags={"Reservations"},
     *     summary="Update reservation details",
     *     description="Updates a reservation and returns it",
     *     security={"bearerAuth": {}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdateReservationRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Resource not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation, UpdateReservation $action): JsonResponse
    {
        return $action->execute($reservation, $request->validated());
    }

    /**
     * Remove the specified reservation from storage.
     *
     * @OA\Delete(
     *     path="/api/reservations/{id}",
     *     operationId="deleteReservation",
     *     tags={"Reservations"},
     *     summary="Delete a reservation",
     *     description="Deletes a reservation",
     *     security={"bearerAuth": {}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(response=204, description="Successful operation"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Resource not found")
     * )
     */
    public function destroy(Reservation $reservation, DeleteReservation $action): JsonResponse
    {
        $this->authorize('delete', $reservation);

        return $action->execute($reservation);
    }

    /**
     * Cancel a reservation.
     *
     * @OA\Patch(
     *     path="/api/reservations/{id}/cancel",
     *     operationId="cancelReservation",
     *     tags={"Reservations"},
     *     summary="Cancel a reservation",
     *     description="Changes the status of a reservation to cancelled",
     *     security={"bearerAuth": {}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Resource not found")
     * )
     */
    public function cancel(Reservation $reservation, CancelReservation $action): JsonResponse
    {
        $this->authorize('cancel', $reservation);

        return $action->execute($reservation);
    }

    /**
     * Confirm a reservation.
     *
     * @OA\Patch(
     *     path="/api/reservations/{id}/confirm",
     *     operationId="confirmReservation",
     *     tags={"Reservations"},
     *     summary="Confirm a reservation",
     *     description="Changes the status of a reservation to confirmed",
     *     security={"bearerAuth": {}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reservation ID",
     *         required=true,
     *
     *         @OA\Schema(type="string", format="ulid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(ref="#/components/schemas/Reservation")
     *     ),
     *
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Resource not found")
     * )
     */
    public function confirm(Reservation $reservation, ConfirmReservation $action): JsonResponse
    {
        $this->authorize('confirm', $reservation);

        return $action->execute($reservation);
    }
}
