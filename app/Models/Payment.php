<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Database\Factories\PaymentFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $reservation_id
 * @property Money $amount Сума в копійках
 * @property string $currency
 * @property Carbon|null $paid_at
 * @property PaymentMethod $payment_method
 * @property PaymentStatus $status
 * @property string|null $transaction_id
 * @property array|null $transaction_details
 * @property Money|null $refunded_amount Сума повернення в копійках
 * @property Carbon|null $refunded_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Reservation $reservation
 *
 * @method static PaymentFactory factory($count = null, $state = [])
 * @method static Builder<static>|Payment newModelQuery()
 * @method static Builder<static>|Payment newQuery()
 * @method static Builder<static>|Payment query()
 * @method static Builder<static>|Payment whereAmount($value)
 * @method static Builder<static>|Payment whereCreatedAt($value)
 * @method static Builder<static>|Payment whereId($value)
 * @method static Builder<static>|Payment wherePaidAt($value)
 * @method static Builder<static>|Payment wherePaymentMethod($value)
 * @method static Builder<static>|Payment whereReservationId($value)
 * @method static Builder<static>|Payment whereStatus($value)
 * @method static Builder<static>|Payment whereTransactionId($value)
 * @method static Builder<static>|Payment whereUpdatedAt($value)
 *
 * @mixin IdeHelperPayment
 *
 * @method static Builder<static>|Payment onlyTrashed()
 * @method static Builder<static>|Payment whereCurrency($value)
 * @method static Builder<static>|Payment whereDeletedAt($value)
 * @method static Builder<static>|Payment whereRefundedAmount($value)
 * @method static Builder<static>|Payment whereRefundedAt($value)
 * @method static Builder<static>|Payment whereTransactionDetails($value)
 * @method static Builder<static>|Payment withTrashed()
 * @method static Builder<static>|Payment withoutTrashed()
 *
 * @mixin Eloquent
 */
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the reservation that owns the payment.
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * Check if the payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::COMPLETED;
    }

    /**
     * Check if the payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === PaymentStatus::PENDING;
    }

    /**
     * Check if the payment is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === PaymentStatus::PROCESSING;
    }

    /**
     * Check if the payment has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === PaymentStatus::FAILED;
    }

    /**
     * Check if the payment has been refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === PaymentStatus::REFUNDED;
    }

    /**
     * Check if the payment has been partially refunded.
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->status === PaymentStatus::PARTIALLY_REFUNDED;
    }

    /**
     * Check if the payment is disputed.
     */
    public function isDisputed(): bool
    {
        return $this->status === PaymentStatus::DISPUTED;
    }

    /**
     * Get the net amount after refunds.
     */
    public function getNetAmount(): Money
    {
        if (! $this->refunded_amount) {
            return $this->amount;
        }

        $netAmount = $this->amount->getAmount() - $this->refunded_amount->getAmount();

        return Money::USD(max(0, $netAmount));
    }

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'amount' => MoneyIntegerCast::class,
            'refunded_amount' => MoneyIntegerCast::class,
            'paid_at' => 'datetime:Y-m-d H:i',
            'refunded_at' => 'datetime:Y-m-d',
            'payment_method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'transaction_details' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}
