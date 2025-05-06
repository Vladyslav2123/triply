<?php

namespace App\Actions\Review;

use App\Models\Review;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GetReview
{
    /**
     * Cache constants
     */
    private const CACHE_TAG = 'reviews';

    private const CACHE_TTL = 3600; // 1 hour

    private const CACHE_KEY_PREFIX = 'review:';

    /**
     * Get a specific review with its relationships.
     *
     * @param  Review  $review  The review to get
     * @return Review The review with loaded relationships
     *
     * @throws Exception If fetching the review fails
     */
    public function execute(Review $review): Review
    {
        try {
            $cacheKey = self::CACHE_KEY_PREFIX.$review->id;

            return Cache::tags([self::CACHE_TAG])->remember(
                $cacheKey,
                self::CACHE_TTL,
                function () use ($review) {
                    return $review->load(['reviewer', 'reservation.reservationable']);
                }
            );
        } catch (Exception $e) {
            Log::error('Failed to get review', [
                'review_id' => $review->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception('Failed to get review: '.$e->getMessage(), 500, $e);
        }
    }
}
