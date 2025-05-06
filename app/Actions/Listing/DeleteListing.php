<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Exception;
use RuntimeException;

class DeleteListing
{
    /**
     * @throws Exception
     */
    public function execute(Listing $listing): void
    {
        try {
            $listing->delete();

        } catch (Exception $e) {
            throw new RuntimeException('Failed to delete listing: '.$e->getMessage(), 500);
        }
    }
}
