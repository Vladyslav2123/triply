<?php

namespace App\Casts;

use App\ValueObjects\Listing\AcceptGuest;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class AcceptGuestCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?AcceptGuest
    {
        if (! $value) {
            return null;
        }

        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return AcceptGuest::fromArray($data);
    }

    /**
     * @throws JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (! $value) {
            return null;
        }

        if (is_array($value)) {
            $value = AcceptGuest::fromArray($value);
        }

        if ($value instanceof AcceptGuest) {
            return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
        }

        return null;
    }
}
