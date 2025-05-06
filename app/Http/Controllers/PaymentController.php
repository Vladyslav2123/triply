<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Reservation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Payments",
 *     description="API Endpoints for managing payments"
 * )
 */
class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     *
     * @OA\Get(
     *     path="/api/v1/payments",
     *     operationId="listPayments",
     *     tags={"Payments"},
     *     summary="Get a list of user's payments",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Filter by start date (Y-m-d)",
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="Filter by end date (Y-m-d)",
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="payment_method",
     *         in="query",
     *         description="Filter by payment method",
     *
     *         @OA\Schema(type="string", enum={"credit_card", "paypal", "bank_transfer"})
     *     ),
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by payment status",
     *
     *         @OA\Schema(type="string", enum={"pending", "processing", "completed", "failed", "refunded", "partially_refunded", "disputed"})
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of payments",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="data", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="amount", type="number", format="float"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Payment::class);

        $query = Payment::query()
            ->whereHas('reservation', function ($query) use ($request) {
                $query->where('guest_id', $request->user()->id);
            })
            ->with('reservation.reservationable');

        if ($request->has('from')) {
            $query->where('paid_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->where('paid_at', '<=', $request->to);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest('paid_at')->paginate();

        return PaymentResource::collection($payments);
    }

    /**
     * Store a newly created payment in storage.
     *
     * @OA\Post(
     *     path="/api/v1/reservations/{reservation}/payments",
     *     operationId="storePayment",
     *     tags={"Payments"},
     *     summary="Create a new payment for a reservation",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="reservation",
     *         in="path",
     *         required=true,
     *         description="Reservation ID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/StorePaymentRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Payment created successfully",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
     * )
     */
    public function store(StorePaymentRequest $request, Reservation $reservation): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request, $reservation) {
                $payment = new Payment([
                    'reservation_id' => $reservation->id,
                    'amount' => $request->amount,
                    'currency' => 'USD', // Default currency
                    'payment_method' => $request->payment_method,
                    'status' => PaymentStatus::COMPLETED,
                    'paid_at' => now(),
                    'transaction_id' => $request->transaction_id ?? null,
                    'transaction_details' => $request->transaction_details ?? null,
                ]);

                $payment->save();

                if ($reservation->isFullyPaid()) {
                    $reservation->update(['status' => ReservationStatus::PAID]);
                }

                return (new PaymentResource($payment))
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (Exception $e) {
            Log::error('Payment creation failed', [
                'reservation_id' => $reservation->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Payment processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified payment in storage.
     *
     * @OA\Put(
     *     path="/api/v1/payments/{payment}",
     *     operationId="updatePayment",
     *     tags={"Payments"},
     *     summary="Update a payment (admin only)",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/UpdatePaymentRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Payment not found"),
     *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
     * )
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): PaymentResource
    {
        try {
            DB::transaction(function () use ($request, $payment) {
                $payment->update($request->validated());

                if (in_array($request->status, [PaymentStatus::REFUNDED->value, PaymentStatus::PARTIALLY_REFUNDED->value])) {
                    if (! $payment->refunded_at) {
                        $payment->refunded_at = now();
                        $payment->save();
                    }
                }
            });

            return new PaymentResource($payment->fresh()->load('reservation.reservationable'));
        } catch (Exception $e) {
            Log::error('Payment update failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Payment update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     *
     * @OA\Get(
     *     path="/api/v1/payments/{payment}",
     *     operationId="showPayment",
     *     tags={"Payments"},
     *     summary="Get details of a specific payment",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment details",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function show(Payment $payment): PaymentResource
    {
        $this->authorize('view', $payment);

        return new PaymentResource($payment->load('reservation.reservationable'));
    }

    /**
     * Remove the specified payment from storage.
     *
     * @OA\Delete(
     *     path="/api/v1/payments/{payment}",
     *     operationId="deletePayment",
     *     tags={"Payments"},
     *     summary="Delete a payment (admin only)",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(response=204, description="Payment deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function destroy(Payment $payment): JsonResponse
    {
        $this->authorize('delete', $payment);

        try {
            $payment->delete();

            return response()->json(null, 204);
        } catch (Exception $e) {
            Log::error('Payment deletion failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Payment deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment for a specific reservation.
     *
     * @OA\Get(
     *     path="/api/v1/reservations/{reservation}/payment",
     *     operationId="getReservationPayment",
     *     tags={"Payments"},
     *     summary="Get payment details for a reservation",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="reservation",
     *         in="path",
     *         required=true,
     *         description="Reservation ID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment details",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="id", type="string", format="uuid"),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function getReservationPayment(Reservation $reservation): JsonResponse
    {
        if ($reservation->guest_id !== auth()->id() &&
            ! ($reservation->reservationable &&
               property_exists($reservation->reservationable, 'host_id') &&
               $reservation->reservationable->host_id === auth()->id()) &&
            ! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $payment = $reservation->payment;

        if (! $payment) {
            return response()->json(['message' => 'No payment found for this reservation'], 404);
        }

        return response()->json(new PaymentResource($payment));
    }

    /**
     * Get payments for host's listings/experiences.
     *
     * @OA\Get(
     *     path="/api/v1/host/payments",
     *     operationId="getHostPayments",
     *     tags={"Payments"},
     *     summary="Get payments for host's listings/experiences",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Filter by start date (Y-m-d)",
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="Filter by end date (Y-m-d)",
     *
     *         @OA\Schema(type="string", format="date")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="List of payments for host's properties",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="data", type="array", @OA\Items(type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="amount", type="number", format="float"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             ))
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function getHostPayments(Request $request): AnonymousResourceCollection
    {
        if (! $request->user()->isHost() && ! $request->user()->isAdmin()) {
            abort(403, 'Only hosts can access this endpoint');
        }

        $hostId = $request->user()->id;

        $query = Payment::query()
            ->whereHas('reservation.reservationable', function ($query) use ($hostId) {
                $query->where('host_id', $hostId);
            })
            ->with('reservation.reservationable', 'reservation.guest');

        if ($request->has('from')) {
            $query->where('paid_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->where('paid_at', '<=', $request->to);
        }

        $payments = $query->latest('paid_at')->paginate();

        return PaymentResource::collection($payments);
    }

    /**
     * Generate a receipt for a payment.
     *
     * @OA\Get(
     *     path="/api/v1/payments/{payment}/receipt",
     *     operationId="generateReceipt",
     *     tags={"Payments"},
     *     summary="Generate a receipt for a payment",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Parameter(
     *         name="payment",
     *         in="path",
     *         required=true,
     *         description="Payment ID",
     *
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Receipt URL",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="receipt_url", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function generateReceipt(Payment $payment): JsonResponse
    {
        $this->authorize('generateReceipt', $payment);

        $receiptUrl = route('api.payments.receipt', ['payment' => $payment->id]);

        return response()->json([
            'receipt_url' => $receiptUrl,
        ]);
    }

    /**
     * Get payment statistics for host.
     *
     * @OA\Get(
     *     path="/api/v1/host/payment-statistics",
     *     operationId="getPaymentStatistics",
     *     tags={"Payments"},
     *     summary="Get payment statistics for host",
     *     security={{
     *         "bearerAuth": {}
     *     }},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Payment statistics",
     *
     *         @OA\JsonContent(type="object",
     *
     *             @OA\Property(property="total_earnings", type="object"),
     *             @OA\Property(property="monthly_earnings", type="object"),
     *             @OA\Property(property="payment_count", type="integer"),
     *             @OA\Property(property="average_payment", type="object"),
     *             @OA\Property(property="monthly_breakdown", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function getPaymentStatistics(Request $request): JsonResponse
    {
        $this->authorize('viewStatistics', Payment::class);

        $hostId = $request->user()->id;

        $payments = Payment::query()
            ->whereHas('reservation.reservationable', function ($query) use ($hostId) {
                $query->where('host_id', $hostId);
            })
            ->where('status', PaymentStatus::COMPLETED)
            ->get();

        $totalEarnings = $payments->sum(function ($payment) {
            return $payment->getNetAmount()->getAmount();
        });

        $monthlyEarnings = $payments
            ->filter(function ($payment) {
                return $payment->paid_at && $payment->paid_at->isCurrentMonth();
            })
            ->sum(function ($payment) {
                return $payment->getNetAmount()->getAmount();
            });

        $averagePayment = $payments->count() > 0
            ? $totalEarnings / $payments->count()
            : 0;

        $monthlyBreakdown = [];
        for ($i = 0; $i < 12; $i++) {
            $month = now()->subMonths($i);
            $monthName = $month->format('F Y');

            $monthlyTotal = $payments
                ->filter(function ($payment) use ($month) {
                    return $payment->paid_at &&
                        $payment->paid_at->year === $month->year &&
                        $payment->paid_at->month === $month->month;
                })
                ->sum(function ($payment) {
                    return $payment->getNetAmount()->getAmount();
                });

            $monthlyBreakdown[$monthName] = [
                'amount' => $monthlyTotal,
                'formatted' => '$'.number_format($monthlyTotal / 100, 2),
            ];
        }

        return response()->json([
            'total_earnings' => [
                'amount' => $totalEarnings,
                'formatted' => '$'.number_format($totalEarnings / 100, 2),
            ],
            'monthly_earnings' => [
                'amount' => $monthlyEarnings,
                'formatted' => '$'.number_format($monthlyEarnings / 100, 2),
            ],
            'payment_count' => $payments->count(),
            'average_payment' => [
                'amount' => $averagePayment,
                'formatted' => '$'.number_format($averagePayment / 100, 2),
            ],
            'monthly_breakdown' => $monthlyBreakdown,
        ]);
    }
}
