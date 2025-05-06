<?php

namespace App\Actions\Review;

use App\Models\Listing;
use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateReview
{
    /**
     * Update an existing review.
     *
     * @param  Review  $review  The review to update
     * @param  array  $data  The validated review data
     * @return Review The updated review
     *
     * @throws Exception If the review update fails
     */
    public function execute(Review $review, array $data): Review
    {
        try {
            // Calculate overall rating
            $data['overall_rating'] = $this->calculateOverallRating($data);

            // Update the review
            $review->update($data);

            // Load relationships
            $review->load(['reviewer', 'reservation.reservationable']);

            // Clear cache
            $this->clearCache($review);

            return $review;
        } catch (Exception $e) {
            Log::error('Failed to update review', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw new Exception('Failed to update review: '.$e->getMessage(), 500, $e);
        }
    }

    /**
     * Calculate the overall rating based on individual ratings.
     *
     * @param  array  $data  The review data
     * @return float The calculated overall rating
     */
    private function calculateOverallRating(array $data): float
    {
        return round(
            (
                $data['cleanliness_rating'] +
                $data['accuracy_rating'] +
                $data['checkin_rating'] +
                $data['communication_rating'] +
                $data['location_rating'] +
                $data['value_rating']
            ) / 6, 1
        );
    }

    /**
     * Clear relevant caches after updating a review.
     *
     * @param  Review  $review  The updated review
     */
    private function clearCache(Review $review): void
    {
        try {
            // Clear reviews cache
            Cache::tags(['reviews'])->flush();

            // Clear specific review cache
            Cache::tags(['reviews'])->forget('review:'.$review->id);

            // Clear listing cache if this review is for a listing
            if ($review->reservation &&
                $review->reservation->reservationable_type === Listing::class) {
                Cache::tags(['listings'])->flush();
            }
        } catch (Exception $e) {
            // Log error but don't throw to prevent breaking the main operation
            Log::error('Failed to clear cache after updating review', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
