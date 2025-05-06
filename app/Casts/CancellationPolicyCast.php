<?php

namespace App\Casts;

use App\ValueObjects\Experience\CancellationPolicy;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use JsonException;

class CancellationPolicyCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get($model, string $key, $value, array $attributes): ?CancellationPolicy
    {
        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return $data ? CancellationPolicy::fromArray($data) : null;
    }

    /**
     * @throws JsonException
     */
    public function set($model, string $key, $value, array $attributes): string|null|false
    {
        if (is_array($value)) {
            $value = CancellationPolicy::fromArray($value);
        }

        if ($value instanceof CancellationPolicy) {
            return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
        }

        return null;
    }
}
