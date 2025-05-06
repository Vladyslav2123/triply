<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role?->value,
            'is_banned' => $this->is_banned,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),

            // Relationships
            'profile' => new ProfileResource($this->whenLoaded('profile')),

            // Counts
            'listings_count' => $this->whenCounted('listings'),
            'reservations_count' => $this->whenCounted('reservations'),
            'reviews_count' => $this->whenCounted('reviews'),
            'favorites_count' => $this->whenCounted('favorites'),
        ];
    }
}
