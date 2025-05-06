<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'overall_rating' => $this->overall_rating,
            'cleanliness_rating' => $this->cleanliness_rating,
            'accuracy_rating' => $this->accuracy_rating,
            'checkin_rating' => $this->checkin_rating,
            'communication_rating' => $this->communication_rating,
            'location_rating' => $this->location_rating,
            'value_rating' => $this->value_rating,
            'comment' => $this->comment,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'reviewer' => [
                'id' => $this->reviewer->id,
                'name' => $this->reviewer->name,
                'photo' => $this->reviewer->photo?->url,
            ],
        ];

        return $data;
    }
}
