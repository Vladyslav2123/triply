<?php

namespace App\Http\Resources;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Listing
 */
class ListingResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type,
            'price_per_night' => $this->price_per_night,
            'photos' => PhotoResource::collection($this->whenLoaded('photos')),
            'discounts' => $this->discounts?->toArray(),
            'accept_guests' => $this->accept_guests?->toArray(),
            'rooms_rules' => $this->rooms_rules?->toArray(),
            'subtype' => $this->subtype,
            'amenities' => $this->amenities,
            'accessibility_features' => $this->accessibility_features?->toArray(),
            'availability_settings' => $this->availability_settings?->toArray(),
            'location' => $this->location?->toArray(),
            'house_rules' => $this->house_rules?->toArray(),
            'guest_safety' => $this->guest_safety?->toArray(),
            'listing_type' => $this->listing_type,
            'status' => $this->status?->value,
            'is_published' => $this->is_published,
            'advance_notice_type' => $this->advance_notice_type?->value,
            'seo' => $this->seo,

            // Relationships
            'host' => new UserResource($this->whenLoaded('host')),
            'availability' => ListingAvailabilityResource::collection($this->whenLoaded('availability')),
            'reservations' => ReservationResource::collection($this->whenLoaded('reservations')),

            // Counts and Ratings
            'photos_count' => $this->whenCounted('photos'),
            'availability_count' => $this->whenCounted('availability'),
            'reservations_count' => $this->whenCounted('reservations'),
            'reviews_count' => $this->whenCounted('reviews_count'),
            'rating' => $this->rating,
            'avg_rating' => $this->when(isset($this->avg_rating), $this->avg_rating),

            // URLs
            'url' => route('listings.show', $this->slug),
        ];
    }
}
