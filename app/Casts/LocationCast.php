<?php

namespace App\Casts;

use App\ValueObjects\Location;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class LocationCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Location
    {
        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return $data ? Location::fromArray($data) : null;
    }

    /**
     * @throws JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string|null|false
    {

        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = Location::fromArray($value);
        }

        if ($value instanceof Location) {
            return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
        }

        return null;
    }
}
