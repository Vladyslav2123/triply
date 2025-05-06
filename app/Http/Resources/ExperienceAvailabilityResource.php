<?php

namespace App\Http\Resources;

use App\Models\ExperienceAvailability;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ExperienceAvailability
 */
class ExperienceAvailabilityResource extends JsonResource
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
            'slots' => $this->spots_available,
            'experience_id' => $this->experience_id,
            'price_override' => $this->price_override,
        ];
    }
}
