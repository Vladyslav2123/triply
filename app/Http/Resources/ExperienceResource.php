<?php

namespace App\Http\Resources;

use App\Models\Experience;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Experience
 */
class ExperienceResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'host_id' => $this->host_id,
            'category' => $this->category?->value,
            'sub_category' => $this->sub_category,
            'location' => $this->location?->toArray(),
            'languages' => $this->languages,
            'status' => $this->status?->value,

            // Relationships
            'host' => new UserResource($this->whenLoaded('host')),
            'photos' => PhotoResource::collection($this->whenLoaded('photos')),
        ];
    }
}
