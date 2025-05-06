<?php

namespace App\Casts;

use App\ValueObjects\Experience\BookingRules;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class BookingRulesCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): BookingRules
    {
        $decoded = json_decode($value, true) ?? [];

        return BookingRules::fromArray($decoded);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        return [
            $key => json_encode(
                $value instanceof BookingRules ? $value->toArray() : $value
            ),
        ];
    }
}
