<?php

namespace App\Casts;

use App\ValueObjects\Experience\GroupDiscounts;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class GroupDiscountsCast implements CastsAttributes
{
    /**
     * Transform raw DB value to GroupDiscounts object.
     */
    public function get($model, string $key, $value, array $attributes): GroupDiscounts
    {
        $decoded = json_decode($value, true) ?? [];

        return GroupDiscounts::fromArray($decoded);
    }

    /**
     * Transform GroupDiscounts object to array for DB storage.
     */
    public function set($model, string $key, $value, array $attributes): array
    {
        return [
            $key => json_encode(
                $value instanceof GroupDiscounts
                    ? $value->toArray()
                    : $value
            ),
        ];
    }
}
