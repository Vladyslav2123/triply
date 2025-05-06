<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reservation = Reservation::query()->inRandomOrder()->first();

        return [
            'reservation_id' => $reservation?->id,
            'amount' => $reservation?->total_price,
            'paid_at' => now(),
            'payment_method' => $this->faker->randomElement(PaymentMethod::all()),
            'status' => PaymentStatus::COMPLETED,
            'currency' => 'USD',
            'transaction_id' => $this->faker->uuid(),
            'transaction_details' => json_encode([
                'processor' => $this->faker->randomElement(['stripe', 'paypal', 'braintree']),
                'customer_id' => $this->faker->uuid(),
                'timestamp' => now()->timestamp,
            ]),
        ];
    }

    /**
     * Create a payment for a specific reservation.
     */
    public function forReservation(Reservation $reservation): static
    {
        return $this->state(function () use ($reservation) {
            return [
                'reservation_id' => $reservation->id,
                'amount' => $reservation->total_price,
            ];
        });
    }

    /**
     * Set a specific payment method.
     */
    public function withPaymentMethod(PaymentMethod $method): static
    {
        return $this->state(function () use ($method) {
            return [
                'payment_method' => $method,
            ];
        });
    }

    /**
     * Set a specific payment status.
     */
    public function withStatus(PaymentStatus $status): static
    {
        return $this->state(function () use ($status) {
            return [
                'status' => $status,
            ];
        });
    }

    /**
     * Set a specific paid date.
     */
    public function paidAt(\DateTime $paidAt): static
    {
        return $this->state(function () use ($paidAt) {
            return [
                'paid_at' => $paidAt,
            ];
        });
    }

    /**
     * Create a payment with pending status.
     */
    public function pending(): static
    {
        return $this->state(function () {
            return [
                'status' => PaymentStatus::PENDING,
                'paid_at' => null,
            ];
        });
    }

    /**
     * Create a payment with processing status.
     */
    public function processing(): static
    {
        return $this->state(function () {
            return [
                'status' => PaymentStatus::PROCESSING,
            ];
        });
    }

    /**
     * Create a payment with completed status.
     */
    public function completed(): static
    {
        return $this->state(function () {
            return [
                'status' => PaymentStatus::COMPLETED,
                'paid_at' => now(),
            ];
        });
    }

    /**
     * Create a payment with failed status.
     */
    public function failed(): static
    {
        return $this->state(function () {
            return [
                'status' => PaymentStatus::FAILED,
                'paid_at' => null,
                'transaction_details' => json_encode([
                    'error_code' => $this->faker->randomElement(['payment_failed', 'card_declined', 'insufficient_funds']),
                    'error_message' => $this->faker->sentence(),
                    'timestamp' => now()->timestamp,
                ]),
            ];
        });
    }

    /**
     * Create a payment with refunded status.
     */
    public function refunded(): static
    {
        return $this->state(function () {
            $amount = $this->faker->numberBetween(1000, 10000);

            return [
                'status' => PaymentStatus::REFUNDED,
                'refunded_amount' => $amount,
                'refunded_at' => now(),
                'transaction_details' => json_encode([
                    'refund_id' => $this->faker->uuid(),
                    'refund_reason' => $this->faker->randomElement(['customer_request', 'duplicate', 'fraudulent']),
                    'timestamp' => now()->timestamp,
                ]),
            ];
        });
    }

    /**
     * Create a payment with partially refunded status.
     */
    public function partiallyRefunded(): static
    {
        return $this->state(function () {
            $amount = $this->faker->numberBetween(1000, 10000);
            $refundedAmount = (int) ($amount * $this->faker->randomFloat(2, 0.1, 0.9));

            return [
                'status' => PaymentStatus::PARTIALLY_REFUNDED,
                'amount' => $amount,
                'refunded_amount' => $refundedAmount,
                'refunded_at' => now(),
                'transaction_details' => json_encode([
                    'refund_id' => $this->faker->uuid(),
                    'refund_reason' => $this->faker->randomElement(['partial_service', 'customer_request', 'service_issue']),
                    'timestamp' => now()->timestamp,
                ]),
            ];
        });
    }

    /**
     * Set a specific transaction ID.
     */
    public function withTransactionId(string $transactionId): static
    {
        return $this->state(function () use ($transactionId) {
            return [
                'transaction_id' => $transactionId,
            ];
        });
    }
}
