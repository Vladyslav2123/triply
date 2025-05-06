<?php

namespace App\QueryBuilders\Reservation;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserReservationQueryBuilder
{
    protected Builder $query;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->query = Reservation::query()->where('guest_id', $user->id);
    }

    /**
     * Get the base query builder instance.
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * Get upcoming reservations.
     */
    public function upcoming(): self
    {
        $this->query->where('check_in', '>=', now())
            ->orderBy('check_in');

        return $this;
    }

    /**
     * Get past reservations.
     */
    public function past(): self
    {
        $this->query->where('check_out', '<', now())
            ->orderByDesc('check_out');

        return $this;
    }

    /**
     * Get current reservations (staying now).
     */
    public function current(): self
    {
        $this->query->where('check_in', '<=', now())
            ->where('check_out', '>=', now());

        return $this;
    }

    /**
     * Filter by status.
     */
    public function withStatus(string $status): self
    {
        $this->query->where('status', $status);

        return $this;
    }

    /**
     * Include reservationable (listing) information.
     */
    public function withReservationable(): self
    {
        $this->query->with('reservationable');

        return $this;
    }

    /**
     * Include payment information.
     */
    public function withPayment(): self
    {
        $this->query->with('payment');

        return $this;
    }

    /**
     * Filter by date range.
     */
    public function betweenDates(string $startDate, string $endDate): self
    {
        $this->query->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('check_in', [$startDate, $endDate])
                ->orWhereBetween('check_out', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('check_in', '<=', $startDate)
                        ->where('check_out', '>=', $endDate);
                });
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
