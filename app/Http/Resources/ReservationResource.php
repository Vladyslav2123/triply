<?php

namespace App\Http\Resources;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Reservation
 */
class ReservationResource extends JsonResource
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
            'guest_id' => $this->guest_id,
            'reservationable_type' => $this->reservationable_type,
            'reservationable_id' => $this->reservationable_id,
            'check_in' => $this->check_in?->format('Y-m-d'),
            'check_out' => $this->check_out?->format('Y-m-d'),
            'total_price' => [
                'amount' => $this->total_price?->getAmount(),
                'currency' => $this->total_price?->getCurrency()->getCode(),
                'formatted' => $this->total_price?->format(),
            ],
            'status' => $this->status?->value,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relationships
            'guest' => new UserResource($this->whenLoaded('guest')),
            'reservationable' => $this->whenLoaded('reservationable', function () {
                if (str_contains($this->reservationable_type, 'Listing')) {
                    return new ListingResource($this->reservationable);
                } elseif (str_contains($this->reservationable_type, 'Experience')) {
                    return new ExperienceResource($this->reservationable);
                }

                return null;
            }),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
            'review' => new ReviewResource($this->whenLoaded('review')),
        ];
    }
}
