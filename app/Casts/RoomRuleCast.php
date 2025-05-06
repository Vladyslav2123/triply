<?php

namespace App\Casts;

use App\ValueObjects\Listing\RoomRule;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class RoomRuleCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?RoomRule
    {
        return $value ? RoomRule::fromArray(json_decode($value, true, 512, JSON_THROW_ON_ERROR)) : null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value instanceof RoomRule ? $value->toArray() : $value, JSON_THROW_ON_ERROR);
    }
}
