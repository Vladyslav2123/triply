<?php

namespace App\Casts;

use App\ValueObjects\Experience\Pricing;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class PricingCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Pricing
    {
        if (is_null($value)) {
            return null;
        }

        $data = json_decode($value, true);

        return Pricing::fromArray($data);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if (is_null($value)) {
            return null;
        }

        if (! $value instanceof Pricing) {
            throw new InvalidArgumentException('The given value is not a Pricing value object.');
        }

        return [
            $key => json_encode($value->toArray(), JSON_UNESCAPED_UNICODE),
        ];
    }
}
