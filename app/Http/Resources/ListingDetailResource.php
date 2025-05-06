<?php

namespace App\Http\Resources;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * @mixin Listing
 */
class ListingDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Отримуємо відгуки через завантажені резервації
        // $reviews = $this->getReviewsFromReservations();
        $reviews = collect();
        // Обчислюємо середні рейтинги
        $averageRatings = $this->calculateAverageRatings($reviews);

        return [
            // Основна інформація про оголошення
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description?->toArray(),
            'price_per_night' => [
                'amount' => $this->price_per_night?->getAmount(),
                'currency' => $this->price_per_night?->getCurrency()->getCode(),
                'formatted' => $this->price_per_night?->format(),
            ],
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
            'type' => $this->type?->value,
            'listing_type' => $this->listing_type,
            'status' => $this->status?->value,
            'is_published' => $this->is_published,
            'advance_notice_type' => $this->advance_notice_type?->value,
            'seo' => $this->seo,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'photos' => PhotoResource::collection($this->whenLoaded('photos')),

            // Рейтинги
            'ratings' => $averageRatings,

            // Господар оголошення з профілем
            'host' => $this->whenLoaded('host', function () {
                return new UserWithProfileResource($this->host);
            }),

            // Відгуки
            'reviews' => ReviewResource::collection($reviews),
            'reviews_count' => $reviews->count(),

            // Доступність
            'availability' => ListingAvailabilityResource::collection($this->whenLoaded('availability')),

            // URL
            'url' => route('listings.show', $this->slug),
        ];
    }

    /**
     * Обчислює середні рейтинги на основі відгуків.
     *
     * @param  Collection  $reviews
     * @return array
     */
    protected function calculateAverageRatings($reviews)
    {
        if ($reviews->isEmpty()) {
            return [
                'overall' => 0,
                'cleanliness' => 0,
                'accuracy' => 0,
                'checkin' => 0,
                'communication' => 0,
                'location' => 0,
                'value' => 0,
            ];
        }

        return [
            'overall' => round($reviews->avg('overall_rating'), 1),
            'cleanliness' => round($reviews->avg('cleanliness_rating'), 1),
            'accuracy' => round($reviews->avg('accuracy_rating'), 1),
            'checkin' => round($reviews->avg('checkin_rating'), 1),
            'communication' => round($reviews->avg('communication_rating'), 1),
            'location' => round($reviews->avg('location_rating'), 1),
            'value' => round($reviews->avg('value_rating'), 1),
        ];
    }

    /**
     * Отримує відгуки з завантажених резервацій.
     */
    protected function getReviewsFromReservations(): Collection
    {
        if (! $this->relationLoaded('reservations')) {
            return collect();
        }

        return $this->reservations
            ->map(function ($reservation) {
                // Завантажуємо review з reviewer.profile якщо ще не завантажено
                if ($reservation->review && ! $reservation->review->relationLoaded('reviewer')) {
                    $reservation->review->load('reviewer.profile');
                }

                return $reservation->review;
            })
            ->filter() // Видаляємо null значення
            ->sortByDesc('created_at'); // Сортуємо за датою створення
    }
}
