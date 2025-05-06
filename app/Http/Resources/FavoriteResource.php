<?php

namespace App\Http\Resources;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /**
         * @var Favorite $favorite
         */
        $favorite = $this->resource;

        return [
            'id' => $favorite->id,
            'user_id' => $favorite->user_id,
            'favoriteable_id' => $favorite->favoriteable_id,
            'favoriteable_type' => class_basename($favorite->favoriteable_type),
            'favoriteable' => $this->whenLoaded('favoriteable'),
            'added_at' => $favorite->added_at->toDate()->format('Y-m-d'),
        ];
    }
}
