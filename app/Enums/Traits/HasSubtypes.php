<?php

namespace App\Enums\Traits;

trait HasSubtypes
{
    public static function allWithSubType(): array
    {
        return array_map(fn ($case) => [
            static::getTypeKey() => $case->value,
            static::getSubtypeKey() => $case->getSubtypes(),
        ], self::cases());
    }

    protected static function getTypeKey(): string
    {
        return 'type';
    }

    protected static function getSubtypeKey(): string
    {
        return 'subtype';
    }

    /**
     * @return array<string>
     */
    abstract public function getSubtypes(): array;
}
