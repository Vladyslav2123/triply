<?php

namespace App\Casts;

use App\ValueObjects\Experience\GuestNeeds;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class GuestNeedsCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): GuestNeeds
    {
        return GuestNeeds::fromArray(json_decode($value ?? '{}', true));
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value instanceof GuestNeeds) {
            return [$key => json_encode($value)];
        }

        return [$key => json_encode((new GuestNeeds(...$value)))];
    }
}
