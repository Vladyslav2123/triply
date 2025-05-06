<?php

namespace App\Http\Resources;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
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
            'reservation_id' => $this->reservation_id,
            'amount' => [
                'amount' => $this->amount?->getAmount(),
                'currency' => $this->amount?->getCurrency()->getCode(),
                'formatted' => $this->amount?->format(),
            ],
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'payment_method' => $this->payment_method?->value,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relationships
            'reservation' => new ReservationResource($this->whenLoaded('reservation')),
        ];
    }
}
