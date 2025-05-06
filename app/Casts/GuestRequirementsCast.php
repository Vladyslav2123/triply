<?php

namespace App\Casts;

use App\ValueObjects\Experience\GuestRequirements;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class GuestRequirementsCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): GuestRequirements
    {
        return GuestRequirements::fromArray(json_decode($value, true) ?? []);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value instanceof GuestRequirements ? $value->toArray() : $value);
    }
}
