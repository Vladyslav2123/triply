<?php

namespace App\Actions\Review;

use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeleteReview
{
    /**
     * Delete a review.
     *
     * @param  Review  $review  The review to delete
     * @return bool True if the review was deleted successfully
     *
     * @throws Exception If the review deletion fails
     */
    public function execute(Review $review): bool
    {
        try {
            // Store reservationable info before deleting the review
            $reservationableId = null;
            $reservationableType = null;
            if ($review->reservation && $review->reservation->reservationable) {
                $reservationableId = $review->reservation->reservationable_id;
                $reservationableType = $review->reservation->reservationable_type;
            }

            // Delete the review
            $result = $review->delete();

            // Clear cache
            $this->clearCache($review->id, $reservationableId, $reservationableType);

            return $result;
        } catch (Exception $e) {
            Log::error('Failed to delete review', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to delete review: '.$e->getMessage(), 500, $e);
        }
    }

    /**
     * Clear relevant caches after deleting a review.
     *
     * @param  string  $reviewId  The ID of the deleted review
     * @param  string|null  $reservationableId  The ID of the reservationable entity
     * @param  string|null  $reservationableType  The type of the reservationable entity
     */
    private function clearCache(string $reviewId, ?string $reservationableId = null, ?string $reservationableType = null): void
    {
        try {
            // Clear reviews cache
            Cache::tags(['reviews'])->flush();

            // Clear specific review cache
            Cache::tags(['reviews'])->forget('review:'.$reviewId);

            // Clear reservationable cache based on type
            if ($reservationableId && $reservationableType) {
                if (str_contains($reservationableType, 'Listing')) {
                    Cache::tags(['listings'])->flush();
                } elseif (str_contains($reservationableType, 'Experience')) {
                    Cache::tags(['experiences'])->flush();
                }
            }
        } catch (Exception $e) {
            // Log error but don't throw to prevent breaking the main operation
            Log::error('Failed to clear cache after deleting review', [
                'review_id' => $reviewId,
                'reservationable_id' => $reservationableId,
                'reservationable_type' => $reservationableType,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
