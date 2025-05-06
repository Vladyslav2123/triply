<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\ReservationFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $guest_id
 * @property string $listing_id
 * @property Carbon $check_in
 * @property Carbon $check_out
 * @property Money $total_price Сума в копійках
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $guest
 * @property-read Listing $listing
 * @property-read Payment|null $payment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payments
 * @property-read Review|null $review
 *
 * @method static ReservationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Reservation newModelQuery()
 * @method static Builder<static>|Reservation newQuery()
 * @method static Builder<static>|Reservation query()
 * @method static Builder<static>|Reservation whereCheckIn($value)
 * @method static Builder<static>|Reservation whereCheckOut($value)
 * @method static Builder<static>|Reservation whereCreatedAt($value)
 * @method static Builder<static>|Reservation whereGuestId($value)
 * @method static Builder<static>|Reservation whereId($value)
 * @method static Builder<static>|Reservation whereListingId($value)
 * @method static Builder<static>|Reservation whereStatus($value)
 * @method static Builder<static>|Reservation whereTotalPrice($value)
 * @method static Builder<static>|Reservation whereUpdatedAt($value)
 *
 * @mixin IdeHelperReservation
 *
 * @property string $reservationable_type
 * @property string $reservationable_id
 * @property-read Model|Eloquent $reservationable
 *
 * @method static Builder<static>|Reservation whereReservationableId($value)
 * @method static Builder<static>|Reservation whereReservationableType($value)
 *
 * @mixin Eloquent
 */

/**
 * @OA\Schema (
 *     title="Reservation",
 *     description="Reservation model",
 *
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="ulid",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="guest_id",
 *         type="string",
 *         format="ulid",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="reservationable_id",
 *         type="string",
 *         format="ulid",
 *         example="01HGW4D3HXN4NCOPY4QJXJ8CVT"
 *     ),
 *     @OA\Property(
 *         property="reservationable_type",
 *         type="string",
 *         example="listing,experience,user"
 *     ),
 *     @OA\Property(
 *         property="check_in",
 *         type="string",
 *         format="date",
 *         example="2024-02-15"
 *     ),
 *     @OA\Property(
 *         property="check_out",
 *         type="string",
 *         format="date",
 *         example="2024-02-20"
 *     ),
 *     @OA\Property(
 *         property="total_price",
 *         type="integer",
 *         description="Сума в копійках",
 *         example=1000000
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "confirmed", "cancelled", "completed"},
 *         example="pending"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-01T00:00:00.000000Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-01T00:00:00.000000Z"
 *     )
 * )
 *
 * @mixin IdeHelperReservation
 *
 * @property string $id
 * @property string $guest_id
 * @property string $reservationable_type
 * @property string $reservationable_id
 * @property Carbon $check_in
 * @property Carbon $check_out
 * @property \Cknow\Money\Money $total_price Сума в копійках
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property ReservationStatus $status
 * @property-read \App\Models\User $guest
 * @property-read \App\Models\Payment|null $payment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Model|\Eloquent $reservationable
 * @property-read \App\Models\Review|null $review
 *
 * @method static Builder<static>|Reservation active()
 * @method static \Database\Factories\ReservationFactory factory($count = null, $state = [])
 * @method static Builder<static>|Reservation newModelQuery()
 * @method static Builder<static>|Reservation newQuery()
 * @method static Builder<static>|Reservation query()
 * @method static Builder<static>|Reservation whereCheckIn($value)
 * @method static Builder<static>|Reservation whereCheckOut($value)
 * @method static Builder<static>|Reservation whereCreatedAt($value)
 * @method static Builder<static>|Reservation whereDeletedAt($value)
 * @method static Builder<static>|Reservation whereGuestId($value)
 * @method static Builder<static>|Reservation whereId($value)
 * @method static Builder<static>|Reservation whereReservationableId($value)
 * @method static Builder<static>|Reservation whereReservationableType($value)
 * @method static Builder<static>|Reservation whereStatus($value)
 * @method static Builder<static>|Reservation whereTotalPrice($value)
 * @method static Builder<static>|Reservation whereUpdatedAt($value)
 *
 * @mixin Eloquent
 */
class Reservation extends Model
{
    /** @use HasFactory<ReservationFactory> */
    use HasFactory, HasUlids;

    /**
     * @var string[]
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guest_id');
    }

    public function reservationable(): MorphTo
    {
        return $this->morphTo();
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class, 'reservation_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'reservation_id');
    }

    /**
     * Get all payments for this reservation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Payment>
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'reservation_id');
    }

    /**
     * Calculate the total amount paid for this reservation.
     */
    public function getTotalPaidAmount(): Money
    {
        $totalPaid = $this->payments()
            ->whereIn('status', [PaymentStatus::COMPLETED, PaymentStatus::PARTIALLY_REFUNDED])
            ->sum('amount');

        $totalRefunded = $this->payments()
            ->whereIn('status', [PaymentStatus::REFUNDED, PaymentStatus::PARTIALLY_REFUNDED])
            ->sum('refunded_amount');

        return Money::USD(max(0, $totalPaid - $totalRefunded));
    }

    /**
     * Check if the reservation is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->getTotalPaidAmount()->greaterThanOrEqual($this->total_price);
    }

    /**
     * Get the remaining balance to be paid.
     */
    public function getRemainingBalance(): Money
    {
        $totalPaid = $this->getTotalPaidAmount();

        if ($totalPaid->greaterThanOrEqual($this->total_price)) {
            return Money::USD(0);
        }

        return $this->total_price->subtract($totalPaid);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            ReservationStatus::CONFIRMED->value,
            ReservationStatus::COMPLETED->value,
        ]);
    }

    public function isCancelled(): bool
    {
        return $this->status === ReservationStatus::CANCELLED_BY_GUEST ||
               $this->status === ReservationStatus::CANCELLED_BY_HOST;
    }

    /**
     * @return array<string,string>
     */
    protected function casts(): array
    {
        return [
            'check_in' => 'datetime:Y-m-d',
            'check_out' => 'datetime:Y-m-d',
            'total_price' => MoneyIntegerCast::class,
            'status' => ReservationStatus::class,
        ];
    }
}
