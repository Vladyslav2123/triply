<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;

class FilterListings
{
    /**
     * @throws Exception
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        try {
            $query = Listing::query()
                ->with(['photos', 'host.profile'])
                ->withAvgRating()
                ->withReviewsCount()
                ->when(
                    isset($filters['min_rating']),
                    fn ($q) => $q->filterByRating($filters['min_rating'])
                )
                ->when(
                    isset($filters['price_min']) || isset($filters['price_max']),
                    fn ($q) => $q->filterByPrice(
                        $filters['price_min'] ?? null,
                        $filters['price_max'] ?? null
                    )
                )
                ->when(
                    isset($filters['type']),
                    fn ($q) => $q->filterByType($filters['type'])
                )
                ->when(
                    isset($filters['location']),
                    fn ($q) => $q->filterByLocation($filters['location'])
                )
                ->when(
                    isset($filters['check_in']) && isset($filters['check_out']),
                    fn ($q) => $q->filterByDates($filters['check_in'], $filters['check_out'])
                )
                ->when(
                    isset($filters['guests']),
                    fn ($q) => $q->filterByGuests($filters['guests'])
                )
                ->when(
                    isset($filters['amenities']),
                    fn ($q) => $q->filterByAmenities($filters['amenities'])
                )
                ->when(
                    isset($filters['accessibility_features']),
                    fn ($q) => $q->filterByAccessibilityFeatures($filters['accessibility_features'])
                )
                ->when(
                    isset($filters['property_size_min']) || isset($filters['property_size_max']),
                    fn ($q) => $q->filterByPropertySize(
                        $filters['property_size_min'] ?? null,
                        $filters['property_size_max'] ?? null
                    )
                )
                ->when(
                    isset($filters['year_built_min']) || isset($filters['year_built_max']),
                    fn ($q) => $q->filterByYearBuilt(
                        $filters['year_built_min'] ?? null,
                        $filters['year_built_max'] ?? null
                    )
                )
                ->when(
                    isset($filters['guest_safety']),
                    fn ($q) => $q->filterByGuestSafety($filters['guest_safety'])
                )
                ->when(
                    isset($filters['search']),
                    fn ($q) => $q->where(function ($query) use ($filters) {
                        $query->where('title', 'like', "%{$filters['search']}%")
                            ->orWhereRaw("description->>'listing_description' ILIKE ?", ["%{$filters['search']}%"])
                            ->orWhereRaw("location->'address'->>'country' ILIKE ?", ["%{$filters['search']}%"])
                            ->orWhereRaw("location->'address'->>'city' ILIKE ?", ["%{$filters['search']}%"])
                            ->orWhereRaw("location->'address'->>'street' ILIKE ?", ["%{$filters['search']}%"])
                            ->orWhereRaw("location->'address'->>'state' ILIKE ?", ["%{$filters['search']}%"]);
                    })
                );

            if (isset($filters['sort'])) {
                $query->applySort($filters['sort']);
            }

            return $query->paginate($filters['per_page'] ?? 10);

        } catch (Exception $e) {
            throw new Exception('Error filtering listings: '.$e->getMessage());
        }
    }
}
