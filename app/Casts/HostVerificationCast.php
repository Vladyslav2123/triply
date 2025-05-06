<?php

namespace App\Casts;

use App\ValueObjects\Experience\HostVerification;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class HostVerificationCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): HostVerification
    {
        return HostVerification::fromArray(json_decode($value ?? '{}', true));
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        if ($value instanceof HostVerification) {
            return json_encode($value->toArray());
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        throw new InvalidArgumentException('The given value is not a HostVerification instance.');
    }
}
