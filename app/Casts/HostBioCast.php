<?php

namespace App\Casts;

use App\ValueObjects\Experience\HostBio;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class HostBioCast implements CastsAttributes
{
    public function get($model, string $key, mixed $value, array $attributes): HostBio
    {
        return HostBio::fromArray(json_decode($value, true) ?? []);
    }

    public function set($model, string $key, mixed $value, array $attributes): array
    {
        return [
            $key => json_encode($value instanceof HostBio ? $value->toArray() : $value),
        ];
    }
}
