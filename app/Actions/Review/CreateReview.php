<?php

namespace App\Actions\Review;

use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CreateReview
{
    /**
     * Create a new review.
     *
     * @param  array  $data  The validated review data
     * @return Review The created review
     *
     * @throws Exception If the review creation fails
     */
    public function execute(array $data): Review
    {
        try {
            // Calculate overall rating
            $data['overall_rating'] = $this->calculateOverallRating($data);

            // Create the review
            $review = Review::create($data);

            // Load relationships
            $review->load(['reviewer', 'reservation.reservationable']);

            // Clear cache
            $this->clearCache($review);

            return $review;
        } catch (Exception $e) {
            Log::error('Failed to create review', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            throw new Exception('Failed to create review: '.$e->getMessage(), 500, $e);
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
     * Clear relevant caches after creating a review.
     *
     * @param  Review  $review  The created review
     */
    private function clearCache(Review $review): void
    {
        try {
            // Clear reviews cache
            Cache::tags(['reviews'])->flush();

            // Clear listing cache if this review is for a listing
            if ($review->reservation &&
                $review->reservation->reservationable_type === 'App\\Models\\Listing') {
                Cache::tags(['listings'])->flush();
            }
        } catch (Exception $e) {
            // Log error but don't throw to prevent breaking the main operation
            Log::error('Failed to clear cache after creating review', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
