<?php

namespace App\Casts;

use App\ValueObjects\Experience\GroupSize;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class GroupSizeCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): GroupSize
    {
        return GroupSize::fromArray(json_decode($value, true));
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        if ($value instanceof GroupSize) {
            return [
                $key => json_encode($value->toArray()),
            ];
        }

        return [
            $key => json_encode($value),
        ];
    }
}
