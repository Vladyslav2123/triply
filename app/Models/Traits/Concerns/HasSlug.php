<?php

namespace App\Models\Traits\Concerns;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Generate a unique slug for the model.
     */
    public function generateSlug(): void
    {
        if (empty($this->slug)) {
            $this->slug = Str::random(15);
        }
    }
}
