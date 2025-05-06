<?php

namespace App\QueryBuilders\Experience;

use App\Enums\ExperienceStatus;
use App\Enums\ExperienceType;
use App\Enums\Language;
use App\Enums\PhysicalActivityLevel;
use App\Enums\SkillLevel;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Builder;

class ExperienceQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = Experience::query();
    }

    /**
     * Get the base query builder instance.
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Filter experiences by status.
     */
    public function withStatus(ExperienceStatus $status): self
    {
        $this->query->where('status', $status);

        return $this;
    }

    /**
     * Filter published experiences.
     */
    public function published(): self
    {
        $this->query->where('status', ExperienceStatus::PUBLISHED);

        return $this;
    }

    /**
     * Filter draft experiences.
     */
    public function draft(): self
    {
        $this->query->where('status', ExperienceStatus::DRAFT);

        return $this;
    }

    /**
     * Filter experiences by category.
     */
    public function withCategory(ExperienceType $category): self
    {
        $this->query->where('category', $category);

        return $this;
    }

    /**
     * Filter experiences by city.
     */
    public function inCity(string $city): self
    {
        $this->query->whereJsonContains('location->address->city', $city);

        return $this;
    }

    /**
     * Filter experiences by language.
     */
    public function withLanguage(Language $language): self
    {
        $this->query->whereJsonContains('languages', $language->value);

        return $this;
    }

    /**
     * Filter experiences by price range.
     */
    public function priceRange(int $min, int $max): self
    {
        $this->query->where('pricing->price_per_person', '>=', $min)
            ->where('pricing->price_per_person', '<=', $max);

        return $this;
    }

    /**
     * Filter experiences by host.
     */
    public function byHost(string $hostId): self
    {
        $this->query->where('host_id', $hostId);

        return $this;
    }

    /**
     * Filter experiences by group size.
     */
    public function withGroupSize(int $size): self
    {
        $this->query->where('grouping->general_group_max', '>=', $size);

        return $this;
    }

    /**
     * Filter experiences by physical activity level.
     */
    public function withPhysicalActivityLevel(PhysicalActivityLevel $level): self
    {
        $this->query->whereJsonContains('guest_requirements->physical_activity_level', $level->value);

        return $this;
    }

    /**
     * Filter experiences by skill level.
     */
    public function withSkillLevel(SkillLevel $level): self
    {
        $this->query->whereJsonContains('guest_requirements->skill_level', $level->value);

        return $this;
    }

    /**
     * Filter experiences by minimum age requirement.
     */
    public function withMinimumAge(int $age): self
    {
        $this->query->where('guest_requirements->minimum_age', '<=', $age);

        return $this;
    }

    /**
     * Filter experiences that allow children under 2.
     */
    public function allowsChildrenUnder2(): self
    {
        $this->query->whereJsonContains('guest_requirements->can_bring_children_under_2', true);

        return $this;
    }

    /**
     * Filter experiences by date range.
     */
    public function availableBetween(string $startDate, string $endDate): self
    {
        $this->query->whereHas('availability', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])
                ->where('is_available', true);
        });

        return $this;
    }

    /**
     * Filter featured experiences.
     */
    public function featured(): self
    {
        $this->query->where('is_featured', true);

        return $this;
    }

    /**
     * Sort experiences by price (low to high).
     */
    public function sortByPriceAsc(): self
    {
        $this->query->orderBy('pricing->price_per_person', 'asc');

        return $this;
    }

    /**
     * Sort experiences by price (high to low).
     */
    public function sortByPriceDesc(): self
    {
        $this->query->orderBy('pricing->price_per_person', 'desc');

        return $this;
    }

    /**
     * Sort experiences by rating (highest first).
     */
    public function sortByRating(): self
    {
        $this->query->orderBy('rating', 'desc');

        return $this;
    }

    /**
     * Sort experiences by newest first.
     */
    public function sortByNewest(): self
    {
        $this->query->orderBy('created_at', 'desc');

        return $this;
    }

    /**
     * Sort experiences by popularity (views count).
     */
    public function sortByPopularity(): self
    {
        $this->query->orderBy('views_count', 'desc');

        return $this;
    }

    /**
     * Include relationships.
     */
    public function with(array $relations): self
    {
        $this->query->with($relations);

        return $this;
    }

    /**
     * Filter experiences by minimum rating.
     */
    public function withMinRating(float $rating): self
    {
        $this->query->where('rating', '>=', $rating);

        return $this;
    }

    /**
     * Search experiences by title or description.
     */
    public function search(string $term): self
    {
        $this->query->where(function ($query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhere('name', 'like', "%{$term}%");
        });

        return $this;
    }
}
