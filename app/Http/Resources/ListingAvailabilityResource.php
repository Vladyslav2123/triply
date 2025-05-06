<?php

namespace App\Http\Resources;

use App\Models\ListingAvailability;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ListingAvailability
 */
class ListingAvailabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date?->format('Y-m-d'),
            'is_available' => $this->is_available,
            'listing_id' => $this->listing_id,
        ];
    }
}
