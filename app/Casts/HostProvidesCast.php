<?php

namespace App\Casts;

use App\ValueObjects\Experience\HostProvides;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class HostProvidesCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?HostProvides
    {
        $data = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

        return $data ? HostProvides::fromArray($data) : null;
    }

    /**
     * @throws JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string|null|false
    {
        if (is_array($value)) {
            $value = HostProvides::fromArray($value);
        }

        if ($value instanceof HostProvides) {
            return json_encode($value->toArray(), JSON_THROW_ON_ERROR);
        }

        return null;
    }
}
