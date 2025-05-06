<?php

namespace App\Actions\Listing;

use App\Enums\ListingStatus;
use App\Models\Listing;
use Exception;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class UnpublishListing
{
    /**
     * Unpublish a listing by setting its status to DRAFT and is_published to false.
     *
     * @param  Listing  $listing  The listing to unpublish
     * @return Listing The updated listing
     *
     * @throws Exception If the listing unpublication fails
     */
    public function execute(Listing $listing): Listing
    {
        try {
            $listing->status = ListingStatus::DRAFT;
            $listing->is_published = false;
            $listing->save();

            return $listing->fresh(['host.profile', 'photos']);
        } catch (Exception $e) {
            Log::error('Failed to unpublish listing', [
                'listing_id' => $listing->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new RuntimeException('Failed to unpublish listing: '.$e->getMessage(), 500, $e);
        }
    }
}
