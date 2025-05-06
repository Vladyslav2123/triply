<?php

namespace App\QueryBuilders\Listing;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserListingQueryBuilder
{
    protected Builder $query;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->query = Listing::query()->where('host_id', $user->id);
    }

    /**
     * Get the base query builder instance.
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Get active listings.
     */
    public function active(): self
    {
        $this->query->where('status', 'active')
            ->orderByDesc('created_at');

        return $this;
    }

    /**
     * Get featured listings.
     */
    public function featured(): self
    {
        $this->query->where('status', 'active')
            ->where('is_featured', true)
            ->orderByDesc('created_at');

        return $this;
    }

    /**
     * Get draft listings.
     */
    public function draft(): self
    {
        $this->query->where('status', 'draft')
            ->orderByDesc('created_at');

        return $this;
    }

    /**
     * Include photos.
     */
    public function withPhotos(): self
    {
        $this->query->with('photos');

        return $this;
    }

    /**
     * Include reviews with average rating.
     */
    public function withReviews(): self
    {
        $this->query->with('reviews')
            ->withAvg('reviews', 'overall_rating');

        return $this;
    }

    /**
     * Filter by price range.
     */
    public function priceRange(float $min, float $max): self
    {
        $this->query->whereBetween('price_per_night', [$min, $max]);

        return $this;
    }

    /**
     * Filter by capacity.
     */
    public function withCapacity(int $guests): self
    {
        $this->query->where('max_guests', '>=', $guests);

        return $this;
    }

    /**
     * Search by title or description.
     */
    public function search(string $term): self
    {
        $this->query->where(function ($query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });

        return $this;
    }

    /**
     * Get the results of the query.
     */
    public function get(array $columns = ['*'])
    {
        return $this->query->get($columns);
    }

    /**
     * Get a paginated result of the query.
     */
    public function paginate(int $perPage = 15, array $columns = ['*'])
    {
        return $this->query->paginate($perPage, $columns);
    }
}
