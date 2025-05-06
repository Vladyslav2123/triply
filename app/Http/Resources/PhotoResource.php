<?php

namespace App\Http\Resources;

use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Photo
 */
class PhotoResource extends JsonResource
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
            'url' => $this->full_url,
            'photoable_type' => $this->photoable_type,
            'photoable_id' => $this->photoable_id,
            'disk' => $this->disk,
            'directory' => $this->directory,
            'size' => $this->size,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'width' => $this->width,
            'height' => $this->height,
            'uploaded_at' => $this->uploaded_at?->format('Y-m-d H:i:s'),
        ];
    }
}
