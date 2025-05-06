<?php

namespace App\Actions\Listing;

use App\Models\Listing;
use Exception;

class UpdateListing
{
    /**
     * @throws Exception
     */
    public function execute(Listing $listing, array $data): Listing
    {
        try {
            $listing->update($data);

            return $listing->fresh();
        } catch (Exception $e) {
            throw new Exception('Failed to update listing: '.$e->getMessage(), 500);
        }
    }
}
