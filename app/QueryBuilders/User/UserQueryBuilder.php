<?php

namespace App\QueryBuilders\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserQueryBuilder
{
    protected Builder $query;

    public function __construct()
    {
        $this->query = User::query();
    }

    /**
     * Get the base query builder instance.
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Filter users by role.
     */
    public function withRole(UserRole $role): self
    {
        $this->query->where('role', $role);

        return $this;
    }

    /**
     * Filter users by email verification status.
     */
    public function emailVerified(): self
    {
        $this->query->whereNotNull('email_verified_at');

        return $this;
    }

    /**
     * Filter users by profile completion status.
     */
    public function withCompleteProfile(): self
    {
        $this->query->whereHas('profile', function ($query) {
            $query->whereNotNull('first_name')
                ->whereNotNull('last_name');
        });

        return $this;
    }

    /**
     * Include profile information.
     */
    public function withProfile(): self
    {
        $this->query->with('profile');

        return $this;
    }

    /**
     * Include photo information.
     */
    public function withPhoto(): self
    {
        $this->query->with('photo');

        return $this;
    }

    /**
     * Search users by name, surname or email.
     */
    public function search(string $term): self
    {
        $this->query->where(function ($query) use ($term) {
            $query->where('email', 'like', "%{$term}%");
        });

        return $this;
    }

    /**
     * Filter users who are hosts.
     */
    public function onlyHosts(): self
    {
        $this->query->where('role', UserRole::HOST);

        return $this;
    }

    /**
     * Filter users who are admins.
     */
    public function onlyAdmins(): self
    {
        $this->query->where('role', UserRole::ADMIN);

        return $this;
    }

    /**
     * Filter users who are regular users.
     */
    public function onlyRegularUsers(): self
    {
        $this->query->where('role', UserRole::USER);

        return $this;
    }

    /**
     * Filter users who have listings.
     */
    public function withListings(): self
    {
        $this->query->whereHas('listings');

        return $this;
    }

    /**
     * Filter users who have reservations.
     */
    public function withReservations(): self
    {
        $this->query->whereHas('reservations');

        return $this;
    }

    /**
     * Filter users who have reviews.
     */
    public function withReviews(): self
    {
        $this->query->whereHas('reviews');

        return $this;
    }

    /**
     * Filter users by creation date range.
     */
    public function createdBetween(string $startDate, string $endDate): self
    {
        $this->query->whereBetween('created_at', [$startDate, $endDate]);

        return $this;
    }

    /**
     * Order by newest.
     */
    public function newest(): self
    {
        $this->query->latest();

        return $this;
    }

    /**
     * Order by oldest.
     */
    public function oldest(): self
    {
        $this->query->oldest();

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

    /**
     * Find a user by ID.
     */
    public function findById(string $id, array $columns = ['*'])
    {
        return $this->query->find($id, $columns);
    }

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email, array $columns = ['*'])
    {
        return $this->query->where('email', $email)->first($columns);
    }

    /**
     * Find a user by slug.
     */
    public function findBySlug(string $slug, array $columns = ['*'])
    {
        return $this->query->where('slug', $slug)->first($columns);
    }
}
