<?php

namespace App\Actions\Listing;

use App\Enums\ListingStatus;
use App\Models\Listing;
use Exception;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PublishListing
{
    /**
     * Publish a listing by setting its status to PUBLISHED and is_published to true.
     *
     * @param  Listing  $listing  The listing to publish
     * @return Listing The updated listing
     *
     * @throws Exception If the listing publication fails
     */
    public function execute(Listing $listing): Listing
    {
        try {
            $listing->status = ListingStatus::PUBLISHED;
            $listing->is_published = true;
            $listing->save();

            return $listing->fresh(['host.profile', 'photos']);
        } catch (Exception $e) {
            Log::error('Failed to publish listing', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new RuntimeException('Failed to publish listing: '.$e->getMessage(), 500, $e);
        }
    }
}
