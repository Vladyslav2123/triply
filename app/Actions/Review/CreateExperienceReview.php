<?php

namespace App\Actions\Review;

use App\Enums\ReservationStatus;
use App\Models\Experience;
use App\Models\Reservation;
use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CreateExperienceReview
{
    /**
     * Create a review for a specific experience.
     *
     * @param  Experience  $experience  The experience to review
     * @param  array  $data  The validated review data
     * @return Review The created review
     *
     * @throws Exception If the review creation fails
     */
    public function execute(Experience $experience, array $data): Review
    {
        try {
            // Find a completed reservation for this experience by the current user
            $reservation = $this->findCompletedReservation($experience);

            if (! $reservation) {
                throw new Exception('You must have a completed reservation to review this experience', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Prepare review data
            $reviewData = $this->prepareReviewData($data, $reservation);

            // Create the review
            $review = Review::create($reviewData);

            // Load relationships
            $review->load(['reviewer', 'reservation.reservationable']);

            // Clear cache
            $this->clearCache($review, $experience);

            return $review;
        } catch (Exception $e) {
            Log::error('Failed to create experience review', [
                'experience_id' => $experience->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Find a completed reservation for the experience by the current user.
     *
     * @param  Experience  $experience  The experience
     * @return Reservation|null The reservation or null if not found
     */
    private function findCompletedReservation(Experience $experience): ?Reservation
    {
        return Reservation::where('reservationable_id', $experience->id)
            ->where('reservationable_type', Experience::class)
            ->where('guest_id', Auth::id())
            ->where('status', ReservationStatus::COMPLETED)
            ->whereDoesntHave('review')
            ->first();
    }

    /**
     * Prepare review data with calculated overall rating.
     *
     * @param  array  $data  The review data
     * @param  Reservation  $reservation  The reservation
     * @return array The prepared review data
     */
    private function prepareReviewData(array $data, Reservation $reservation): array
    {
        $data['reservation_id'] = $reservation->id;
        $data['reviewer_id'] = Auth::id();

        $data['overall_rating'] = round(
            (
                $data['cleanliness_rating'] +
                $data['accuracy_rating'] +
                $data['checkin_rating'] +
                $data['communication_rating'] +
                $data['location_rating'] +
                $data['value_rating']
            ) / 6, 1
        );

        return $data;
    }

    /**
     * Clear relevant caches after creating a review.
     *
     * @param  Review  $review  The created review
     * @param  Experience  $experience  The experience that was reviewed
     */
    private function clearCache(Review $review, Experience $experience): void
    {
        try {
            // Clear reviews cache
            Cache::tags(['reviews'])->flush();

            // Clear experience cache
            Cache::tags(['experiences'])->flush();
        } catch (Exception $e) {
            // Log error but don't throw to prevent breaking the main operation
            Log::error('Failed to clear cache after creating experience review', [
                'review_id' => $review->id,
                'experience_id' => $experience->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
