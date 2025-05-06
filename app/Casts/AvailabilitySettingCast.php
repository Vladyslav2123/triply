<?php

namespace App\Casts;

use App\ValueObjects\Listing\AvailabilitySetting;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AvailabilitySettingCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?AvailabilitySetting
    {
        return $value ? AvailabilitySetting::fromArray(json_decode($value, true, 512, JSON_THROW_ON_ERROR)) : null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value instanceof AvailabilitySetting ? $value->toArray() : $value, JSON_THROW_ON_ERROR);
    }
}
