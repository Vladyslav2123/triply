<?php

namespace App\Casts;

use App\Enums\ExperienceType;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class ExperienceTypeCast implements CastsAttributes
{
    public function get($model, string $key, mixed $value, array $attributes): ?ExperienceType
    {
        return $value ? ExperienceType::from($value) : null;
    }

    public function set($model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value instanceof ExperienceType) {
            return $value->value;
        }

        if (is_string($value) && ExperienceType::tryFrom($value)) {
            return $value;
        }

        throw new InvalidArgumentException("Invalid value for ExperienceType enum: $value");
    }
}
