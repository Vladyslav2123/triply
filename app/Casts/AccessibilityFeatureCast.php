<?php

namespace App\Casts;

use App\ValueObjects\Listing\AccessibilityFeature;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AccessibilityFeatureCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?AccessibilityFeature
    {
        return $value ? AccessibilityFeature::fromArray(json_decode($value, true, 512, JSON_THROW_ON_ERROR)) : null;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value instanceof AccessibilityFeature ? $value->toArray() : $value, JSON_THROW_ON_ERROR);
    }
}
