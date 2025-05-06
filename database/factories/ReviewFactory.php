<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $cleanliness_rating = $this->faker->numberBetween(1, 5);
        $accuracy_rating = $this->faker->numberBetween(1, 5);
        $checkin_rating = $this->faker->numberBetween(1, 5);
        $communication_rating = $this->faker->numberBetween(1, 5);
        $location_rating = $this->faker->numberBetween(1, 5);
        $value_rating = $this->faker->numberBetween(1, 5);

        // Calculate overall rating
        $overall_rating = round(
            ($cleanliness_rating + $accuracy_rating + $checkin_rating +
             $communication_rating + $location_rating + $value_rating) / 6,
            1
        );

        try {
            // Try to get a random reservation
            $reservation = Reservation::query()
                ->whereDoesntHave('review')
                ->inRandomOrder()
                ->firstOrFail();

            return [
                'reservation_id' => $reservation->id,
                'reviewer_id' => $reservation->guest_id,
                'overall_rating' => $overall_rating,
                'cleanliness_rating' => $cleanliness_rating,
                'accuracy_rating' => $accuracy_rating,
                'checkin_rating' => $checkin_rating,
                'communication_rating' => $communication_rating,
                'location_rating' => $location_rating,
                'value_rating' => $value_rating,
                'comment' => $this->faker->optional(0.8)->paragraph(),
            ];
        } catch (ModelNotFoundException $e) {
            // Return state without reservation - it will be set later by forReservation()
            return [
                'reviewer_id' => null, // Буде встановлено пізніше
                'overall_rating' => $overall_rating,
                'cleanliness_rating' => $cleanliness_rating,
                'accuracy_rating' => $accuracy_rating,
                'checkin_rating' => $checkin_rating,
                'communication_rating' => $communication_rating,
                'location_rating' => $location_rating,
                'value_rating' => $value_rating,
                'comment' => $this->faker->optional(0.8)->paragraph(),
            ];
        }
    }

    /**
     * Create a review for a specific reservation.
     */
    public function forReservation(Reservation $reservation): static
    {
        return $this->state(function () use ($reservation) {
            // Ensure the reservation is completed
            if ($reservation->status !== ReservationStatus::COMPLETED) {
                throw new InvalidArgumentException('Cannot create a review for a non-completed reservation.');
            }

            // Ensure the reservation doesn't already have a review
            if ($reservation->review()->exists()) {
                throw new InvalidArgumentException('This reservation already has a review.');
            }

            return [
                'reservation_id' => $reservation->id,
                'reviewer_id' => $reservation->guest_id,
            ];
        });
    }

    /**
     * Create a positive review with high ratings (4-5).
     */
    public function positive(): static
    {
        return $this->state(function () {
            $rating = $this->faker->numberBetween(4, 5);

            return [
                'cleanliness_rating' => $rating,
                'accuracy_rating' => $rating,
                'checkin_rating' => $rating,
                'communication_rating' => $rating,
                'location_rating' => $rating,
                'value_rating' => $rating,
                'overall_rating' => $rating,
                'comment' => $this->faker->paragraph(),
            ];
        });
    }

    /**
     * Create a negative review with low ratings (1-2).
     */
    public function negative(): static
    {
        return $this->state(function () {
            $rating = $this->faker->numberBetween(1, 2);

            return [
                'cleanliness_rating' => $rating,
                'accuracy_rating' => $rating,
                'checkin_rating' => $rating,
                'communication_rating' => $rating,
                'location_rating' => $rating,
                'value_rating' => $rating,
                'overall_rating' => $rating,
                'comment' => $this->faker->paragraph(),
            ];
        });
    }

    /**
     * Create a neutral review with medium ratings (2-4).
     */
    public function neutral(): static
    {
        return $this->state(function () {
            $rating = $this->faker->numberBetween(2, 4);

            return [
                'cleanliness_rating' => $rating,
                'accuracy_rating' => $rating,
                'checkin_rating' => $rating,
                'communication_rating' => $rating,
                'location_rating' => $rating,
                'value_rating' => $rating,
                'overall_rating' => $rating,
                'comment' => $this->faker->paragraph(),
            ];
        });
    }
}
