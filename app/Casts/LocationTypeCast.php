<?php

namespace App\Casts;

use App\Enums\LocationType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class LocationTypeCast implements CastsAttributes
{
    public function get($model, string $key, mixed $value, array $attributes): ?LocationType
    {
        return $value ? LocationType::from($value) : null;
    }

    public function set($model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof LocationType) {
            return $value->value;
        }

        if (is_string($value) && LocationType::tryFrom($value)) {
            return $value;
        }

        throw new InvalidArgumentException('Invalid value for enum LocationType.');
    }
}
