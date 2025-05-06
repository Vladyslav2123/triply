<?php

namespace App\Models\Traits\QueryBuilders;

use App\QueryBuilders\Listing\UserListingQueryBuilder;
use App\QueryBuilders\Reservation\UserReservationQueryBuilder;

/**
 * Trait HasQueryBuilders
 *
 * Містить методи для роботи з QueryBuilder.
 */
trait HasQueryBuilders
{
    /**
     * Get a query builder for the user's listings.
     */
    public function listingsQuery(): UserListingQueryBuilder
    {
        return new UserListingQueryBuilder($this);
    }

    /**
     * Get a query builder for the user's reservations.
     */
    public function reservationsQuery(): UserReservationQueryBuilder
    {
        return new UserReservationQueryBuilder($this);
    }
}
