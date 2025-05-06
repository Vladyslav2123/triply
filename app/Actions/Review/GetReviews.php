<?php

namespace App\Actions\Review;

use App\Models\Experience;
use App\Models\Listing;
use App\Models\Review;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GetReviews
{
    /**
     * Cache constants
     */
    private const CACHE_TAG = 'reviews';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get paginated reviews with optional filtering.
     *
     * @param  Request  $request  The request with filter parameters
     * @return LengthAwarePaginator The paginated reviews
     *
     * @throws Exception If fetching reviews fails
     */
    public function execute(Request $request): LengthAwarePaginator
    {
        try {
            $cacheKey = 'reviews:'.md5(json_encode($request->query()));

            return Cache::tags([self::CACHE_TAG])->remember(
                $cacheKey,
                self::CACHE_TTL,
                function () use ($request) {
                    $query = Review::query()->with(['reviewer', 'reservation.reservationable']);

                    // Filter by listing ID if provided
                    if ($request->has('listing_id')) {
                        $query->whereHas('reservation', function ($q) use ($request) {
                            $q->where('reservationable_id', $request->input('listing_id'))
                                ->where('reservationable_type', Listing::class);
                        });
                    }

                    if ($request->has('experience_id')) {
                        $query->whereHas('reservation', function ($q) use ($request) {
                            $q->where('reservationable_id', $request->input('experience_id'))
                                ->where('reservationable_type', Experience::class);
                        });
                    }

                    // Apply sorting
                    $sortField = $request->input('sort', 'created_at');
                    $sortOrder = $request->input('order', 'desc');
                    $allowedSortFields = ['created_at', 'overall_rating'];

                    if (in_array($sortField, $allowedSortFields)) {
                        $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc');
                    }

                    // Paginate results
                    return $query->paginate($request->input('per_page', 10));
                }
            );
        } catch (Exception $e) {
            Log::error('Failed to get reviews', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->query(),
            ]);

            throw new Exception('Failed to get reviews: '.$e->getMessage(), 500, $e);
        }
    }

    /**
     * Get paginated reviews for a specific listing.
     *
     * @param  Listing  $listing  The listing to get reviews for
     * @param  Request  $request  The request with filter parameters
     * @return LengthAwarePaginator The paginated reviews
     *
     * @throws Exception If fetching reviews fails
     */
    public function forListing(Listing $listing, Request $request): LengthAwarePaginator
    {
        return $this->forReservationable($listing, $request, 'listings');
    }

    /**
     * Get paginated reviews for a specific reservationable entity.
     *
     * @param  mixed  $reservationable  The reservationable entity to get reviews for
     * @param  Request  $request  The request with filter parameters
     * @param  string  $cacheTag  The cache tag to use
     * @return LengthAwarePaginator The paginated reviews
     *
     * @throws Exception If fetching reviews fails
     */
    private function forReservationable($reservationable, Request $request, string $cacheTag): LengthAwarePaginator
    {
        try {
            $cacheKey = strtolower(class_basename($reservationable)).':'.$reservationable->id.':reviews:'.md5(json_encode($request->query()));

            return Cache::tags([self::CACHE_TAG, $cacheTag])->remember(
                $cacheKey,
                self::CACHE_TTL,
                function () use ($request, $reservationable) {
                    $query = Review::query()
                        ->with(['reviewer'])
                        ->whereHas('reservation', function ($q) use ($reservationable) {
                            $q->where('reservationable_id', $reservationable->id)
                                ->where(function ($subq) use ($reservationable) {
                                    $subq->where('reservationable_type', get_class($reservationable))
                                        ->orWhere('reservationable_type', strtolower(class_basename($reservationable)));
                                });
                        });

                    $sortField = $request->input('sort', 'created_at');
                    $sortOrder = $request->input('order', 'desc');
                    $allowedSortFields = ['created_at', 'overall_rating'];

                    if (in_array($sortField, $allowedSortFields)) {
                        $query->orderBy($sortField, $sortOrder === 'asc' ? 'asc' : 'desc');
                    }

                    return $query->paginate($request->input('per_page', 10));
                }
            );
        } catch (Exception $e) {
            Log::error('Failed to get reviews for '.strtolower(class_basename($reservationable)), [
                strtolower(class_basename($reservationable)).'_id' => $reservationable->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->query(),
            ]);

            throw new Exception('Failed to get reviews for '.strtolower(class_basename($reservationable)).': '.$e->getMessage(), 500, $e);
        }
    }

    /**
     * Get paginated reviews for a specific experience.
     *
     * @param  Experience  $experience  The experience to get reviews for
     * @param  Request  $request  The request with filter parameters
     * @return LengthAwarePaginator The paginated reviews
     *
     * @throws Exception If fetching reviews fails
     */
    public function forExperience(Experience $experience, Request $request): LengthAwarePaginator
    {
        return $this->forReservationable($experience, $request, 'experiences');
    }
}
