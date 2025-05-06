<?php

namespace App\Actions\Review;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CreateListingReview
{
    /**
     * Create a review for a specific listing.
     *
     * @param  Listing  $listing  The listing to review
     * @param  array  $data  The validated review data
     * @return Review The created review
     *
     * @throws Exception If the review creation fails
     */
    public function execute(Listing $listing, array $data): Review
    {
        try {
            // Find a completed reservation for this listing by the current user
            $reservation = $this->findCompletedReservation($listing);

            if (! $reservation) {
                throw new Exception('You must have a completed reservation to review this listing', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Prepare review data
            $reviewData = $this->prepareReviewData($data, $reservation);

            // Create the review
            $review = Review::create($reviewData);

            // Load relationships
            $review->load(['reviewer', 'reservation.reservationable']);

            // Clear cache
            $this->clearCache($review, $listing);

            return $review;
        } catch (Exception $e) {
            Log::error('Failed to create listing review', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw $e;
        }
    }

    /**
     * Find a completed reservation for the listing by the current user.
     *
     * @param  Listing  $listing  The listing
     * @return Reservation|null The reservation or null if not found
     */
    private function findCompletedReservation(Listing $listing): ?Reservation
    {
        return Reservation::where('reservationable_id', $listing->id)
            ->where('reservationable_type', Listing::class)
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
     * @param  Listing  $listing  The listing that was reviewed
     */
    private function clearCache(Review $review, Listing $listing): void
    {
        try {
            // Clear reviews cache
            Cache::tags(['reviews'])->flush();

            // Clear listing cache
            Cache::tags(['listings'])->flush();
        } catch (Exception $e) {
            // Log error but don't throw to prevent breaking the main operation
            Log::error('Failed to clear cache after creating listing review', [
                'review_id' => $review->id,
                'listing_id' => $listing->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
